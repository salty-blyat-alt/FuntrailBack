<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatController extends Controller
{
    public function getTotalSalesThisWeek()
    {
        // Get the authenticated user's ID
        $userId = Auth::id();
        $hotel = Hotel::where('user_id', $userId)->first();
        $hotelId = $hotel->id;

        // Get the start and end of the current week
        $startOfWeek = Carbon::now()->startOfWeek(); // Default is Monday
        $endOfWeek = Carbon::now()->endOfWeek(); // Default is Sunday

        // Query to sum the total sales for the current week for the authenticated user
        $totalSalesCurrentWeek = Booking::where('hotel_id', $hotelId)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->sum('total');

        // Get the start and end of the previous week
        $startOfLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfLastWeek = Carbon::now()->subWeek()->endOfWeek();

        // Query to sum the total sales for the previous week for the authenticated user
        $totalSalesLastWeek = Booking::where('hotel_id', $hotelId)
            ->whereBetween('created_at', [$startOfLastWeek, $endOfLastWeek])
            ->sum('total');

        // Calculate the percentage change
        $percentageChange = 0;
        if ($totalSalesLastWeek > 0) {
            $percentageChange = (($totalSalesCurrentWeek - $totalSalesLastWeek) / $totalSalesLastWeek) * 100;
        }

        return $this->successResponse([
            'total_sales_current_week' => $totalSalesCurrentWeek,
            'total_sales_last_week' => $totalSalesLastWeek,
            'percentage_change' => round($percentageChange, 2),
        ]);
    }

    public function getTotalSalesThisMonth()
    {
        // Get the authenticated user's ID
        $userId = Auth::id();
        $hotel = Hotel::where('user_id', $userId)->first();
        $hotelId = $hotel->id;

        // Get the start and end of the current month
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Query to sum the total sales for the current month for the authenticated user
        $totalSalesCurrentMonth = Booking::where('hotel_id', $hotelId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total');

        // Get the start and end of the previous month
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        // Query to sum the total sales for the previous month for the authenticated user
        $totalSalesLastMonth = Booking::where('hotel_id', $hotelId)
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('total');

        // Calculate the percentage change
        $percentageChange = 0;
        if ($totalSalesLastMonth > 0) {
            $percentageChange = (($totalSalesCurrentMonth - $totalSalesLastMonth) / $totalSalesLastMonth) * 100;
        }

        return $this->successResponse([
            'total_sales_current_month' => $totalSalesCurrentMonth,
            'total_sales_last_month' => $totalSalesLastMonth,
            'percentage_change' => round($percentageChange, 2),
        ]);
    }

    public function pendingOrders()
    {
        $userId = Auth::id();
        $hotel = Hotel::where('user_id', $userId)->first();
        $hotelId = $hotel->id;

        // Get the current date
        $currentDate = Carbon::now();

        // Query to get all pending orders with date_end less than the current date
        $pendingOrders = Booking::with(['room', 'user']) // Eager load room and user relationships
            ->where('date_end', '<', $currentDate)
            ->where('hotel_id', $hotelId)
            ->get();

        // Transform the results to include room type and username
        $pendingOrdersWithDetails = $pendingOrders->map(function ($order) {
            return [
                'id' => $order->id,
                'room_id' => $order->room_id,
                'hotel_id' => $order->hotel_id,
                'date_start' =>  Carbon::parse($order->date_start)->format('d/m/Y'),
                'date_end' =>   Carbon::parse($order->date_end)->format('d/m/Y'),
                'total' => $order->total,
                'ordered_at' => Carbon::parse($order->created_at)->format('d/m/Y'),
                'room_type' => $order->room->room_type ?? null, // Assuming 'type' is the field for room type
                'username' => $order->user->username ?? null, // Assuming 'name' is the field for username
            ];
        });

        return $this->successResponse($pendingOrdersWithDetails);
    }

    // TODO 
    public function ordersHistory(Request $request)
    {
        $userId = Auth::id();
        $hotel = Hotel::where('user_id', $userId)->firstOrFail();
        $hotelId = $hotel->id;

        // Get the perPage value from the request, default to 10 if not provided
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);

        $orderHistory = DB::table('bookings as b')
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
            ->where('b.hotel_id', $hotelId)
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

        $total = $orderHistory->count();
        $orderHistory = $orderHistory->forPage($page, $perPage);

        $paginatedOrderHistory = new LengthAwarePaginator(
            $orderHistory,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $orderHistory = cleanPagination($paginatedOrderHistory);


        // Prepare the response
        return $this->successResponse($orderHistory);
    }
}
