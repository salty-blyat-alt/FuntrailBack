<?php

namespace App\Http\Controllers\Book;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Commission;
use App\Models\Hotel;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator; 
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $hotel_id = (int) $request->hotel_id;
        $hotel = Hotel::where('user_id', $customer_id)->first();
        $date_start = Carbon::createFromFormat('d/m/Y', $request->date_start)->format('Y-m-d');
        $date_end = Carbon::createFromFormat('d/m/Y', $request->date_end)->format('Y-m-d');

        // Check room availability 
        if (!$this->isRoomAvailable($request->room_ids, $date_start, $date_end, $request->hotel_id)) {
            return $this->errorResponse('No rooms available');
        }

        $isHotelOwner = ($user_type === 'hotel' && $hotel->user_id === $customer_id && $hotel->id === $hotel_id);

        if ($isHotelOwner) {
            $bookings =  $this->ownerBook($request);
            return $this->successResponse("Rooms booked successfully");
        } else {
            $bookings =  $this->customerBook($request);
            return $this->successResponse($bookings);
        }
    }

    public function ownerBook(object $request)
    {
        $roomIds = $request->room_ids;
        $customer_id = $request->user()->id;
        $date_start = Carbon::createFromFormat('d/m/Y', $request->date_start)->format('Y-m-d');
        $hotel_id = (int) $request->hotel_id;
        $hotel = Hotel::where('user_id', $customer_id)->first();
        $user_type = $request->user()->user_type;
        $date_end = Carbon::createFromFormat('d/m/Y', $request->date_end)->format('Y-m-d');
        $uuid = rand(1, 40000); 
        $isHotelOwner = ($user_type === 'hotel' && $hotel->user_id === $customer_id && $hotel->id === $hotel_id);


        $rooms = Room::whereIn('id', $roomIds)->get();
        foreach ($rooms as $room) {
            $room->status = 'busy';
            $room->save();
        }

        // Use a transaction for atomicity

        $bookings = $this->saveRecords($roomIds, $hotel_id, $customer_id, $date_start, $date_end, $uuid, $isHotelOwner);

        // Create commission record
        $commission = Commission::create([
            'user_id' => $customer_id,
            'payment_type' => 'Stripe',
            'total_payment' => 0.00,
            'commission_rate' => 0,
            'booking_id' => $uuid,
            'total_commission' => 0.00,
        ]);

        return $bookings;
    }

    public function customerBook(object $request)
    {
        $customer_id = $request->user()->id;
        $date_start = Carbon::createFromFormat('d/m/Y', $request->date_start);
        // $hotel_id = (int) $request->hotel_id;
        // $hotel = Hotel::where('user_id', $customer_id)->first();
        // $user_type = $request->user()->user_type;
        $date_end = Carbon::createFromFormat('d/m/Y', $request->date_end);
        $uuid = rand(1, 4000000000);
 
        // Calculate total cost and commission
        $pre_commission = $this->calculateTotalCost($request->room_ids, $date_start, $date_end);

        $total_cost = ($pre_commission * 0.05) + $pre_commission;

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        // Prepare line items for Stripe
        $lineItems = [];
        foreach ($request->room_ids as $room_id) {
            $room = Room::find($room_id);
            $lineItems[] = [
                'price_data' => [
                'currency' => 'usd',
                    'product_data' => [
                        'name' => "Room: {$room->room_type}", 
                        'description' => "Booking for room type: {$room->room_type}", // Option
                    ],
                    'unit_amount' => $total_cost * 100, 
                ],
                'quantity' => 1, 
            ];
        }

        // Create Stripe Checkout session
        $session = \Stripe\Checkout\Session::create([
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => env('FRONTEND_URL') . '/hotel-detail/' . $request->hotel_id . '?session_id={CHECKOUT_SESSION_ID}', // Correctly set success URL
            'cancel_url' => route('checkout.cancel', [], true),
            'metadata' => [
                'hotel_id' => $request->hotel_id,
                'user_id' => $customer_id,
                'date_start' => $date_start,
                'date_end' => $date_end,
                'uuid' => $uuid,
                'room_ids' => json_encode($request->room_ids),
                'pre_commission' => $pre_commission,
                'total_cost' => $total_cost,
            ],
        ]);


        // Save booking records
        $this->saveRecords($request->room_ids, $request->hotel_id, $customer_id, $date_start, $date_end, $uuid, false);

        // Return both the session and bookings
        return $session->url;
    }

    public function success(string $session_id)
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $sessionId = $session_id; // Assign the parameter to a variable

        // Start a database transaction
        DB::beginTransaction();

        try {
            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            if (!$session) {
                throw new NotFoundHttpException('Session not found');
            }

            // Retrieve the ID to look for in the database
            $id = (int) $session->metadata->uuid; // Assuming this is the ID to look for

            // Retrieve the bookings using the ID
            $orders = Booking::where('id', $id)->get();
            // Debugging output
            // dd($session);

            // Check if any orders were found
            if ($orders->isEmpty()) {
                Log::error('Order not found for ID: ' . $id);
                throw new NotFoundHttpException('Order not found for ID: ' . $id);
            }

            // Loop through each order and update the status if necessary
            foreach ($orders as $order) {
                if ($order->status === 'pending') {
                    $order->status = 'completed';
                    $order->save(); // Save the updated order status
                }
            }

            // Decode room IDs from metadata
            $roomIds = json_decode($session->metadata->room_ids);

            // Loop through each room ID and update their status
            foreach ($roomIds as $room_id) {
                $room = Room::find($room_id); 
   
                if ($room) {
                    $room->status = 'busy'; 
                    $room->save();
                } else {
                    Log::warning('Room not found for ID: ' . $room_id);
                }
            }

            // Create commission record
            Commission::create([
                'user_id' => $session->metadata->user_id,
                'payment_type' => 'Stripe',
                'total_payment' => $session->metadata->pre_commission,
                'commission_rate' => 5,
                'booking_id' => $session->metadata->uuid,
                'total_commission' => $session->metadata->total_cost -  $session->metadata->pre_commission,
            ]);

            // Commit the transaction
            DB::commit();

            // Redirect to Next.js app with session ID
            return $this->successResponse("Rooms booked successfully");
        } catch (\Exception $e) {
            // Roll back the transaction if any error occurs
            DB::rollBack();

            Log::error('Error in the success method: ' . $e->getMessage());
            throw new NotFoundHttpException('An error occurred while processing the payment.');
        }
    }

    public function isRoomAvailable($room_ids, $date_start, $date_end, $hotel_id)
    {
        foreach ($room_ids as $room_id) {
            $room = Room::where('hotel_id', $hotel_id)->where('id', $room_id)->first();
            if (!$room) {
                return false;
            }

            $booking_exists = Booking::where('room_id', $room_id)->where('status', 'completed')
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
                    'status' => $isByOwner ? 'completed' : 'pending',
                ];

                $booking = Booking::create($bookingData);
                $booking->id = $uuid;
                $bookings[] = $booking;
            }
        }
        return $bookings;
    }

    public function calculateTotalCost(array $room_ids, $date_start, $date_end)
    {
        $total_cost = 0;
        $nights = $date_start->diffInDays($date_end);

        foreach ($room_ids as $room_id) {
            $room = Room::find($room_id);
            if ($room) {
                // Calculate cost for each room over the duration
                $total_cost += $room->price_per_night * $nights;
            } else {
                throw new \Exception('Room not found for ID: ' . $room_id);
            }
        }

        return $total_cost;
    }
}
