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
                'bookings.id',
                'bookings.room_id',
                'bookings.hotel_id',
                'bookings.user_id',
                'bookings.date_start',
                'bookings.date_end',
                'bookings.total',
                'bookings.status',
                'bookings.u_id',
                'bookings.created_at',
                'bookings.updated_at',
                'rooms.room_type',
                'rooms.price_per_night',
                'rooms.img as room_img',
                'hotels.name as hotel_name'
            ])
            ->get();

        // Current date for comparison
        $now = Carbon::now();

        // Separate bookings into active and history
        $activeOrders = [];
        $historyOrders = [];

        // Format the result and format the dates to 'dd/mm/yyyy'
        foreach ($bookings as $booking) {
            $formattedBooking = [
                'id' => $booking->id,
                'room_id' => $booking->room_id,
                'hotel_id' => $booking->hotel_id,
                'user_id' => $booking->user_id,
                'date_start' => Carbon::parse($booking->date_start)->format('d/m/Y'), // Format date_start
                'date_end' => Carbon::parse($booking->date_end)->format('d/m/Y'), // Format date_end
                'total' => $booking->total,
                'status' => $booking->status,
                'u_id' => $booking->u_id,
                'created_at' => Carbon::parse($booking->created_at)->format('d/m/Y'), // Format created_at
                'updated_at' => Carbon::parse($booking->updated_at)->format('d/m/Y'), // Format updated_at
                'room_type' => $booking->room_type,
                'price_per_night' => $booking->price_per_night,
                'hotel_name' => $booking->hotel_name,
                'room_img' => $booking->room_img
            ];

            // Compare date_end with current date (now)
            if (Carbon::parse($booking->date_end)->lt($now)) {
                // If the booking has passed, it's a history booking
                $historyOrders[] = $formattedBooking;
            } else {
                // If it's still active
                $activeOrders[] = $formattedBooking;
            }
        }

        // Return the formatted response with active orders and history orders
        return $this->successResponse([
            'active_orders' => $activeOrders,
            'history_orders' => $historyOrders,
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

    // Update the specified booking in storage
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
