<?php

namespace App\Http\Controllers;

use App\Models\Province;
use Illuminate\Http\Request;

class ProvinceController extends Controller
{
    public function popular($limit = 10)
{
    $provinces = Province::withCount('hotels')
        ->withCount('restaurants') 
        ->orderBy('hotels_count', 'desc') 
        ->orderBy('restaurants_count', 'desc') 
        ->take($limit)
        ->get();

    // Return the provinces as a JSON response
    return response()->json($provinces);
}




    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all provinces
        $provinces = Province::all();

        // Return a JSON response with the provinces
        return response()->json($provinces);
    }
    public function show(string $id)
    {
        // Find the province by ID
        $province = Province::find($id);

        // Check if province exists
        if (!$province) {
            return response()->json(['message' => 'Province not found'], 404);
        }

        // Return the province data
        return response()->json($province);
    }
}
