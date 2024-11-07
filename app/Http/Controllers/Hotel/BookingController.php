<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

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

        $bookings = DB::table('bookings as b')
            ->leftJoin('commissions as c', 'c.booking_id', '=', 'b.id')
            ->leftJoin('users as u', 'u.id', '=', 'b.user_id')
            ->leftJoin('rooms as r', 'r.id', '=', 'b.room_id')
            ->leftJoin('hotels as h', 'h.id', '=', 'b.hotel_id')
            ->select(
                'b.id as receipt_id',
                'h.name as hotel_name',
                'u.username as username',
                'r.room_type as room_type',
                'b.date_start as checkin',
                'b.date_end as checkout',
                'c.total_payment as total',
                'b.created_at as ordered_at',
            )
            ->where('b.user_id', $user->id) 
            ->orderBy('b.created_at', 'desc') 
            ->get()
            ->groupBy('receipt_id')
            ->map(function ($group) {
                $first = $group->first();
                $first->ordered_at = Carbon::parse($first->ordered_at);
                $first->ordered_at = $first->ordered_at->diffForHumans(Carbon::now());
                return [
                    'receipt_id' => $first->receipt_id,
                    'hotel_name' => $first->hotel_name,
                    'username' => $first->username,
                    'checkin' => $first->checkin,
                    'checkout' => $first->checkout,
                    'total' => $first->total,
                    'ordered_at' => $first->ordered_at,
                    'rooms' => $group->pluck('room_type')->toArray()
                ];
            })
            ->values();
 

        return $this->successResponse($bookings);
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
