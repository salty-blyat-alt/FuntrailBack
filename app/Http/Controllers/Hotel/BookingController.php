<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BookingController extends Controller
{

    public function history(Request $request)
    {
        // Get the authenticated user using Sanctum
        $user = $request->user();

        // Check if user exists and is authenticated
        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        // Use joins to retrieve related room and hotel data
        $bookings = Booking::leftJoin('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->leftJoin('hotels', 'bookings.hotel_id', '=', 'hotels.id')
            ->where('bookings.user_id', $user->id)
            ->where('bookings.status', 'completed')
            ->select([
                'bookings.id',          // Booking ID (not unique)
                'bookings.room_id',
                'bookings.hotel_id',
                'bookings.user_id',
                'bookings.date_start',
                'bookings.date_end',
                'bookings.total',
                'bookings.status',
                'bookings.u_id',       // Receipt ID
                'bookings.created_at',
                'bookings.updated_at',
                'rooms.room_type',
                'rooms.price_per_night',
                'rooms.img as room_img',
                'hotels.name as hotel_name'
            ])
            ->get();

        // Prepare the response structure
        $response = [
            'active_orders' => [],
            'history_orders' => [],
        ];

        // Prepare a temporary array to group orders
        $groupedOrders = [];
      
        foreach ($bookings as $booking) {
            // Prepare the booking data
            $bookingData = [
                // Use u_id as receipt ID
                'room_id' => $booking->room_id,
                'room_type' => $booking->room_type,
                'price_per_night' => $booking->price_per_night,
                'room_img' => $booking->room_img,
            ]; 
            // Hoist hotel-related data
            $hotelData = [
                'receipt_id' => $booking->u_id,
                'hotel_id' => $booking->hotel_id,
                'hotel_name' => $booking->hotel_name,
                'date_start' => Carbon::parse($booking->date_start)->format('d/m/Y'),
                'date_end' => Carbon::parse($booking->date_end)->format('d/m/Y'),
                'created_at' => $booking->created_at,
                'updated_at' => $booking->updated_at,
                'total' =>  $booking->price_per_nigt,  // total cost of the rooms 
            ];

            // Determine whether to classify as an active or history order
            if (Carbon::parse($booking->date_end)->isFuture()) {
                // Push to active orders, group by hotel and dates
                $key = $booking->hotel_id . '|' . $booking->date_start . '|' . $booking->date_end;

                if (!isset($groupedOrders['active_orders'][$key])) {
                    $groupedOrders['active_orders'][$key] = array_merge($hotelData, ['rooms' => []]);
                }

                $groupedOrders['active_orders'][$key]['rooms'][] = $bookingData;
            } else {
                // Push to history orders
                $key = $booking->hotel_id . '|' . $booking->date_start . '|' . $booking->date_end;

                if (!isset($groupedOrders['history_orders'][$key])) {
                    $groupedOrders['history_orders'][$key] = array_merge($hotelData, ['rooms' => []]);
                }

                $groupedOrders['history_orders'][$key]['rooms'][] = $bookingData;
            }
        }

        // Reset the active_orders and history_orders to the grouped data
        $response['active_orders'] = array_values($groupedOrders['active_orders']);
        $response['history_orders'] = array_values($groupedOrders['history_orders']);

        return response()->json([
            'result' => true,
            'result_code' => 200,
            'result_message' => 'Success',
            'body' => $response,
        ]);
    }




    // Display a listing of bookings
    public function index()
    {
        $bookings = Booking::all();
        return response()->json($bookings);
    }

    // Store a newly created booking in storage
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'room_type' => 'required|string',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after_or_equal:check_in_date',
            'total_price' => 'required|numeric|min:0',
        ]);

        $booking = Booking::create($request->all());

        return response()->json($booking, Response::HTTP_CREATED);
    }

    // Display the specified booking
    public function show($id)
    {
        $booking = Booking::findOrFail($id);
        return response()->json($booking);
    }

    // Update the specified booking in storageq
    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'room_type' => 'required|string',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after_or_equal:check_in_date',
            'total_price' => 'required|numeric|min:0',
        ]);

        $booking = Booking::findOrFail($id);
        $booking->update($request->all());

        return response()->json($booking);
    }

    // Remove the specified booking from storage
    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
