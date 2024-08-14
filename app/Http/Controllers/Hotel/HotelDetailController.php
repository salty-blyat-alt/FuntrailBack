<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\HotelDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HotelDetailController extends Controller
{
    /**
     * Display a listing of the resource.
    */
    public function index()
    {
        $hotelDetails = HotelDetail::with('hotel', 'roomType')->get();
        return response()->json($hotelDetails);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'hotel_id' => 'required|integer|exists:hotels,id',
            'room_type_id' => 'required|integer|exists:room_types,id',
            'image' => 'nullable|string',
            'is_available' => 'required|boolean',
        ]);

        $hotelDetail = HotelDetail::create($request->all());

        return response()->json($hotelDetail, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $hotelDetail = HotelDetail::with('hotel', 'roomType')->findOrFail($id);
        return response()->json($hotelDetail);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'hotel_id' => 'required|integer|exists:hotels,id',
            'room_type_id' => 'required|integer|exists:room_types,id',
            'image' => 'nullable|string',
            'is_available' => 'required|boolean',
        ]);

        $hotelDetail = HotelDetail::findOrFail($id);
        $hotelDetail->update($request->all());

        return response()->json($hotelDetail);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $hotelDetail = HotelDetail::findOrFail($id);
        $hotelDetail->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
