<?php

namespace App\Http\Controllers\Hotel;

use App\Models\Hotel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class HotelController extends Controller
{
    // tested  DONE WORK
    public function popular($limit = 10) {
     $response = Hotel::withCount('bookings')
        ->orderBy('bookings_count', 'desc')
        ->take($limit)
        ->get();

    return response()->json($response);
    }

        // tested  DONE WORK
    public function index()
    {
        $hotels = Hotel::all();
        return response()->json($hotels);
    }

    // tested  DONE WORK
    public function store(Request $request)
{
    try {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'required|integer|exists:users,id',
            'province_id' => 'required|integer|exists:provinces,id',
            'address' => 'required|string|max:255',
            'description' => 'nullable|string',
            'room_available' => 'required|integer|min:0',
            'phone_number' => 'required|string',
            'image' => 'nullable|url',
            'open_at' => 'required|date_format:H:i',
            'close_at' => 'required|date_format:H:i|after:open_at',
        ]);

        // Create the hotel record
        $hotel = Hotel::create($validatedData);

        // Return a success response with the created hotel
        return response()->json($hotel, 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Return validation error response
        return response()->json([
            'errors' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        // Handle general exceptions and return a server error response
        return response()->json([
            'error' => 'An unexpected error occurred.',
            'message' => $e->getMessage()
        ], 500);
    }
}


    // Display the specified resource
    public function show(Hotel $hotel)
    {
        return response()->json($hotel);
    }

    // Update the specified resource in storage
    public function update(Request $request, Hotel $hotel)
    {
        $request->validate($this->hotelRules());

        $hotel->update($request->all());

        return response()->json($hotel);
    }

    // Remove the specified resource from storage
    public function destroy(Hotel $hotel)
    { 
        $hotel->delete();

        return response()->json(['message' => 'Hotel deleted successfully'], 200);    }
        
         

}
