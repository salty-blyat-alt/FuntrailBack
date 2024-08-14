<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    public function index( )
    {
        $hotels = Hotel::all();
        return response()->json($hotels);
    }
    public function store(Request $request)
    {
        $request->validate(($this->hotelRules()));
        Hotel::created($request);
        return response()->json( 201);
    }
    
    public function update(Request $request, Hotel $hotel)
    {
        $request->validate(($this->hotelRules()));
        $hotel->update($request->all());
        return response()->json($hotel);
    }
        
         

}
