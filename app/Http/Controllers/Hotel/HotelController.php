<?php

namespace App\Http\Controllers\Hotel;

use App\Models\Hotel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HotelController extends Controller
{
    public function search(Request $request)
    {
        // work
        $query = Hotel::select('id', 'name', 'room_available', 'image', 'open_at','close_at');

        // work
        if ($request->has('province_id') && $request->input('province_id') !== '') {
            $query->where('province_id', $request->input('province_id'));
        }

        // work
        if ($request->has('room_available')) {
            $query->where('room_available', '>=', $request->input('room_available'));
        }

        // work
         if ($request->has('name') && $request->input('name') !== '') {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($request->input('name')) . '%']);
        }

        // Filter by room type price range if provided
        if ($request->has('price_min') && $request->has('price_max')) {
            $query->whereHas('hotelDetails', function ($q) use ($request) {
                $q->whereHas('roomType', function ($q) use ($request) {
                    $q->whereBetween('price', [$request->input('price_min'), $request->input('price_max')]);
                });
            });
        }

        // Eager load the related models to include room type prices
        $hotels = $query->with(['hotelDetails.roomType'])->get();

        // Transform the data to include room type prices
        $hotels = $hotels->map(function ($hotel) {
             $hotelDetails = $hotel->hotelDetails->map(function ($detail) {
                return [
                    'room_type_id' => $detail->room_type_id,
                    'room_type_name' => $detail->roomType->room_type,
                    'price' => $detail->roomType->price,
                    'image' => $detail->image,
                    'is_available' => $detail->is_available,
                ];
            });

            return [
                'id' => $hotel->id,
                'name' => $hotel->name,
                'user_id' => $hotel->user_id,
                'province_id' => $hotel->province_id,
                'address' => $hotel->address,
                'description' => $hotel->description,
                'room_available' => $hotel->room_available,
                'phone_number' => $hotel->phone_number,
                'image' => $hotel->image,
                'open_at' => $hotel->open_at,
                'close_at' => $hotel->close_at,
                'hotel_details' => $hotelDetails,
            ];
        });


        return response()->json($hotels);
    }




    // tested  DONE WORK
    public function popular($limit = 20)
    {
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
                'image' => 'nullable|file|image|mimes:jpeg,png,jpg',
                'open_at' => 'required|date_format:H:i',
                'close_at' => 'required|date_format:H:i|after:open_at',
            ]);


            if ($request->hasFile('image')) {
                // Store the image and get the path
                $imagePath = $request->file('image')->store('hotels', 'public');
                $validatedData['image'] = $imagePath; // Add image path to validated data
            } else {
                $validatedData['image'] = null; // Ensure image field is null if no file is uploaded
            }


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

        return response()->json(['message' => 'Hotel deleted successfully'], 200);
    }
}
