<?php

namespace App\Http\Controllers\Book;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Commission;
use App\Models\Hotel;
use App\Models\Room;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{

    public function book(Request $request)
    {
        // dd($request->all());
        $validatedData = Validator::make($request->all(), $this->bookingRules());
        if ($validatedData->fails()) {
            info($validatedData->messages());
            return $this->errorResponse('Fail to book');
        }

        $customer_id = $request->user()->id;
        $user_type = $request->user()->user_type;
        $uuid = rand(1, 4000000000);
        $request->hotel_id = (int) $request->hotel_id;
        $hotel = Hotel::where('user_id', $customer_id)->first();
        $date_start = Carbon::createFromFormat('d/m/Y', $request->date_start)->format('Y-m-d');
        $date_end = Carbon::createFromFormat('d/m/Y', $request->date_end)->format('Y-m-d');


        // Check room availability 
        if (!$this->isRoomAvailable($request->room_ids, $date_start, $date_end, $request->hotel_id)) {
            return $this->errorResponse('No rooms available');
        }

        // Check if the user is the owner of the hotel 
        $isHotelOwner = ($user_type === 'hotel' && $hotel->user_id === $customer_id && $hotel->id === $request->hotel_id);
        if ($isHotelOwner) {
            $roomIds = $request->room_ids;
            $rooms = Room::whereIn('id', $roomIds)->get();
            foreach ($rooms as $room) {
                $room->status = 'busy';
                $room->save();
            }
            $bookings = $this->saveRecords($request->room_ids, $request->hotel_id, $customer_id, $date_start, $date_end, $uuid);
            return $this->successResponse($bookings);
        }

        // for regular customer
        DB::beginTransaction();
        try {
            $total_cost = 0;

            $total_cost = $this->calculateTotalCost($request->room_ids);

            $total_commission = $total_cost * 0.05;
            $total_cost = ($total_cost * 0.05) + $total_cost;

            // stripe only accept int
            $session = $this->stripePay($total_cost, $request->user()->id, $request->hotel_id, $request->room_ids);


            Commission::create([
                'user_id' => $customer_id,
                'payment_type' => 'Stripe',
                'total_payment' => $total_cost,
                'commission_rate' =>  5,
                'total_commission' => $total_commission,
            ]);

            // Check user balance before saving records
            if ($request->user()->balance < $total_cost) {
                DB::rollBack();
                return $this->errorResponse('Insufficient balance', 400);
            }

            $bookings = $this->saveRecords($request->room_ids, $request->hotel_id, $customer_id, $date_start, $date_end, $uuid, true);

            // Deduct balance after successful booking
            $request->user()->balance -= $total_cost;
            $request->user()->save();

            DB::commit();

            return $this->successResponse([
                'result' => $bookings,
                'session' => $session
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
        return $this->successResponse($bookings);
    }
    public function isRoomAvailable($room_ids, $date_start, $date_end, $hotel_id)
    {
        foreach ($room_ids as $room_id) {
            $room = Room::where('hotel_id', $hotel_id)->where('id', $room_id)->first();
            if (!$room) {
                return false;
            }

            $booking_exists = Booking::where('room_id', $room_id)
                ->where(function ($query) use ($date_start, $date_end) {
                    $query->whereBetween('date_start', [$date_start, $date_end])
                        ->orWhereBetween('date_end', [$date_start, $date_end])
                        ->orWhere(function ($query) use ($date_start, $date_end) {
                            $query->where('date_start', '<=', $date_start)
                                ->where('date_end', '>=', $date_end);
                        });
                })
                ->exists();

            if ($booking_exists) {
                return false;
            }
        }
        return true;
    }

    // work done (chain with "book" func)
    public function saveRecords($room_ids, $hotel_id, $customer_id, $date_start, $date_end, $uuid, $isByOwner = false)
    {
        $bookings = []; // Initialize bookings array

        foreach ($room_ids as $room_id) {
            $room = Room::find($room_id);

            if ($room) {
                // Create a booking record
                $bookingData = [
                    'id' => $uuid,
                    'room_id' => $room_id,
                    'hotel_id' => $hotel_id,
                    'user_id' => $customer_id,
                    'date_start' => $date_start,
                    'date_end' => $date_end,
                    'total' => $room->price_per_night,
                ];

                if ($isByOwner) {
                    $bookingData['status'] = 'completed';
                }

                $booking = Booking::create($bookingData);
                $booking->id = $uuid;
                $bookings[] = $booking;
            }
        }

        return $bookings;
    }

    public function calculateTotalCost(array $room_ids)
    {
        $total_cost = 0;

        foreach ($room_ids as $room_id) {
            $room = Room::find($room_id);
            if ($room) {
                $total_cost += $room->price_per_night;
            } else {
                throw new \Exception('Room not found for ID: ' . $room_id);
            }
        }

        return $total_cost;
    }

    public function stripePay($amount, $user_id, $hotel_id, $room_ids)
    {
        try {
            $total_cost_in_cents = intval($amount * 100);

            // Stripe
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => $total_cost_in_cents,
                        'product_data' => [
                            'name' => 'Hotel Booking',
                            'description' => 'Booking for Hotel ID: ' . $hotel_id,
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'success_url' => env('FRONTEND_URL') . '?session_id={CHECKOUT_SESSION_ID}', // Include session ID in the success URL
                'mode' => 'payment',
                'metadata' => [
                    'user_id' => $user_id,
                    'hotel_id' => $hotel_id,
                    'room_ids' => json_encode($room_ids),
                ],
            ]);



            return [
                'status' => 'success',
                'message' => 'Payment session created successfully',
                'session_id' => $session->id,
                'payment_url' => $session->url,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error creating payment session: ' . $e->getMessage(),
            ];
        }
    }
}
