<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller
{
    use ValidatesRequests;
    public function hotelRules(){
        return [ 
            'name' => 'required|string',
            'location' => 'required|string',
            'total_rooms' => 'required|integer',
            'bookings_count' => 'required|integer',
            'average_rating' => 'required|numeric|between:0,5',
            'revenue' => 'required|numeric',
        ];
    } 
}
