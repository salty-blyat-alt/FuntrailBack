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
use Stripe\PaymentIntent;
use Stripe\Stripe;

class BookController extends Controller
{

    public function book(Request $request)
    {
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
        $hotel_owner_id = $hotel->user_id;


        $date_start = Carbon::createFromFormat('d/m/Y', $request->date_start)->format('Y-m-d');
        $date_end = Carbon::createFromFormat('d/m/Y', $request->date_end)->format('Y-m-d');


        // Check room availability 
        if (!$this->isRoomAvailable($request->room_ids, $date_start, $date_end, $request->hotel_id)) {
            return $this->errorResponse('No rooms available');
        }


        // Check if the user is the owner of the hotel 
        if (
            $user_type === 'hotel' &&
            $hotel_owner_id === $customer_id &&
            $hotel->id === $request->hotel_id
        ) {
            $bookings = $this->saveRecords($request->room_ids, $request->hotel_id, $customer_id, $date_start, $date_end, $uuid);
            return $this->successResponse($bookings);
        }


        // for regular customer
        DB::beginTransaction();
        try {
            $total_cost = 0;

            // Calculate total cost first
            foreach ($request->room_ids as $room_id) {
                $room = Room::find($room_id);
                if ($room) {
                    $total_cost += $room->price_per_night;
                } else {
                    DB::rollBack();
                    return $this->errorResponse('Room not found', 404);
                }
            }

            $total_commission = $total_cost * 0.05;
            $total_cost = ($total_cost * 0.05) + $total_cost;

            // stripe only accept int
            $payment_intend = $this->stripePay($total_cost, $request->user()->id, $request->hotel_id, $request->room_ids);

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

            $bookings = $this->saveRecords($request->room_ids, $request->hotel_id, $customer_id, $date_start, $date_end, $uuid);


            // Deduct balance after successful booking
            $request->user()->balance -= $total_cost;

            $request->user()->save();

            DB::commit();

            return $this->successResponse([
                'result' => $bookings,
                'client_secret' => $payment_intend->client_secret,
                'intend_id' => $payment_intend->id
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
            if ($room) {
                return !Booking::where('room_id', $room_id)
                    ->where(function ($query) use ($date_start, $date_end) {
                        $query->whereBetween('date_start', [$date_start, $date_end])
                            ->orWhereBetween('date_end', [$date_start, $date_end])
                            ->orWhere(function ($query) use ($date_start, $date_end) {
                                $query->where('date_start', '<=', $date_start)
                                    ->where('date_end', '>=', $date_end);
                            });
                    })
                    ->exists();
            }
        }
        return true;
    }

    // work done (chain with "book" func)
    public function saveRecords($room_ids, $hotel_id, $customer_id, $date_start, $date_end, $uuid)
    {
        $bookings = []; // Initialize bookings array

        foreach ($room_ids as $room_id) {
            $room = Room::find($room_id);

            if ($room) {
                // Create a booking record
                $booking = Booking::create([
                    'id' => $uuid,
                    'room_id' => $room_id,
                    'hotel_id' => $hotel_id,
                    'user_id' => $customer_id,
                    'date_start' => $date_start,
                    'date_end' => $date_end,
                    'total' => $room->price_per_night,
                ]);

                // Update the room status to 'busy' or 'occupied'
                $room->status = 'busy';
                $room->save();

                $booking->id = $uuid;
                $bookings[] = $booking;
            }
        }

        return $bookings;
    }

    public function stripePay($amount, $user_id, $hotel_id, $room_ids)
    {
        $total_cost_in_cents = intval($amount * 100);

        // stripe
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $paymentIntent = PaymentIntent::create([
            'amount' => $total_cost_in_cents,
            'currency' => 'usd',
            'metadata' => [
                'user_id'   => $user_id,
                'hotel_id'  => $hotel_id,
                'room_ids'  => json_encode($room_ids)
            ]
        ]);
        return $paymentIntent;
    }
    
}
