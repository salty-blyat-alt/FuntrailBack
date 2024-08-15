<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function popular($limit = 10)
    {
        // Get the top $limit restaurants with the highest order count
        $response = Restaurant::withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->take($limit)
            ->get();

        return response()->json($response);
    }





    
    public function index()
    {
        $restaurants = Restaurant::all();
        return response()->json($restaurants);
    }


//    tested DONE WORK
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'user_id' => 'required|exists:users,id',
                'province_id' => 'required|integer|exists:provinces,id',
                'address' => 'required|string|max:255',
                'description' => 'nullable|string',
                'phone_number' => 'nullable|string|max:15',
                'image' => 'nullable|file|image|mimes:jpeg,png,jpg',
                'open_at' => 'required|date_format:H:i',
                'close_at' => 'required|date_format:H:i',
            ]);

            if ($request->hasFile('image')) {
                // Store the image and get the path
                $imagePath = $request->file('image')->store('restaurants', 'public');
                $validatedData['image'] = $imagePath; // Add image path to validated data
            } else {
                $validatedData['image'] = null; // Ensure image field is null if no file is uploaded
            }


            $restaurant = Restaurant::create($request->all());




            return response()->json($restaurant, 201);


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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $restaurant = Restaurant::findOrFail($id);
        return response()->json($restaurant);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'user_id' => 'sometimes|required|exists:users,id',
            'province' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'phone_number' => 'sometimes|nullable|string|max:15',
            'image' => 'sometimes|nullable|url',
            'open_at' => 'sometimes|required|date_format:H:i:s',
            'close_at' => 'sometimes|required|date_format:H:i:s',
        ]);

        $restaurant = Restaurant::findOrFail($id);
        $restaurant->update($request->all());

        return response()->json($restaurant);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $restaurant = Restaurant::findOrFail($id);
        $restaurant->delete();

        return response()->json(null, 204);
    }
}
