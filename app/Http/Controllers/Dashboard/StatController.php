<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function ordersHistory(Request $request)
    {
        $userId = Auth::id();
        $hotel = Hotel::where('user_id', $userId)->firstOrFail();
        $hotelId = $hotel->id;
    
        // Get the perPage value from the request, default to 10 if not provided
        $perPage = $request->input('perPage', 10);
    
        // Paginate the results directly
        $orderHistory = Booking::with(['room', 'user'])
            ->where('hotel_id', $hotelId)
            ->paginate($perPage);
    
        // Transform the results to include room type and username
        $ordersHistory = $orderHistory->getCollection()->map(function ($order) {
            return [
                'b_id' => $order->id,
                'id' => $order->u_id ?? null,
                'room_id' => $order->room_id,
                'hotel_id' => $order->hotel_id,
                'date_start' => Carbon::parse($order->date_start)->format('d/m/Y'),
                'date_end' => Carbon::parse($order->date_end)->format('d/m/Y'),
                'total' => $order->total,
                'ordered_at' => Carbon::parse($order->created_at)->format('d/m/Y'),
                'room_type' => $order->room->room_type ?? null,
                'username' => $order->user->username ?? null, 
            ];
        });
    
        // Prepare the response
        return $this->successResponse([
            'items' => $ordersHistory,
            'paginate' => [
                'total' => $orderHistory->total(),
                'per_page' => $orderHistory->perPage(),
                'current_page' => $orderHistory->currentPage(),
                'next_page_url' => $orderHistory->nextPageUrl(),
                'last_page' => $orderHistory->lastPage(),
            ]
        ]);
    }
    
}
