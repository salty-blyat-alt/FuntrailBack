<?php

namespace App\Http\Controllers\Hotel;

use App\Models\Hotel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class HotelController extends Controller
{
    public function index()
    {
        $hotels = Hotel::all();
        return response()->json($hotels);
    }

    // Store a newly created resource in storage
    public function store(Request $request)
    {
        $request->validate($this->hotelRules());

        $hotel = Hotel::create($request->all());

        return response()->json($hotel, 201);
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
