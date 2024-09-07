<?php

namespace App\Http\Controllers\Hotel;

use App\Models\Hotel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Booking;



class HotelController extends Controller
{
    public function search(Request $request)
    {
        $query = Hotel::select('id', 'name', 'room_available', 'image', 'open_at', 'close_at');

        if ($request->has('province_id') && $request->input('province_id') !== '') {
            $query->where('province_id', $request->input('province_id'));
        }
  
        if ($request->has('name') && $request->input('name') !== '') {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($request->input('name')) . '%']);
        }

        if ($request->has('price_min') && $request->has('price_max')) {
            $query->whereHas('hotelDetails', function ($q) use ($request) {
                $q->whereHas('roomType', function ($q) use ($request) {
                    $q->whereBetween('price', [$request->input('price_min'), $request->input('price_max')]);
                });
            });
        }

        $hotels = $query->with(['hotelDetails.roomType'])->get();

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
                'room_available' => $hotel->room_available,
                'hotel_details' => $hotelDetails,
            ];
        });

        return response()->json($hotels);
    }

    public function popular($limit = 20)
    {
        $hotels = Booking::select('hotel_id')
            ->groupBy('hotel_id')
            ->orderBy('total_bookings', 'DESC')
            ->take($limit)
            ->get();

        return response()->json($hotels);
    }

    public function index()
    {
        $hotels = Hotel::all();
        return response()->json($hotels);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'user_id' => 'required|integer|exists:users,id',
                'province_id' => 'required|integer|exists:provinces,id',
                'address' => 'required|string|max:255',
                'description' => 'nullable|string',
                'phone_number' => 'required|string',
                'thumbnail' => 'nullable|file|image|mimes:jpeg,png,jpg',
                'images' => 'nullable|array',
                'images.*' => 'file|image|mimes:jpeg,png,jpg',
                'open_at' => 'required|date_format:H:i',
                'close_at' => 'required|date_format:H:i|after:open_at',
            ]);

            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $request->file('thumbnail')->store('hotels/thumbnails', 'public');
                $validatedData['thumbnail'] = $thumbnailPath;
            }

            if ($request->has('images')) {
                $imagesPaths = [];
                foreach ($request->file('images') as $image) {
                    $imagesPaths[] = $image->store('hotels/images', 'public');
                }
                $validatedData['images'] = json_encode($imagesPaths); // Store as JSON
            } else {
                $validatedData['images'] = null; // Ensure images field is null if no files are uploaded
            }

            $hotel = Hotel::create($validatedData);
            return response()->json($hotel, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred.', 'message' => $e->getMessage()], 500);
        }
    }

    public function show(Hotel $hotel)
    {
        return response()->json($hotel);
    }

    public function update(Request $request, Hotel $hotel)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'user_id' => 'sometimes|required|integer|exists:users,id',
            'province_id' => 'sometimes|required|integer|exists:provinces,id',
            'address' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'phone_number' => 'sometimes|required|string',
            'thumbnail' => 'nullable|file|image|mimes:jpeg,png,jpg',
            'images' => 'nullable|array',
            'images.*' => 'file|image|mimes:jpeg,png,jpg',
            'open_at' => 'sometimes|required|date_format:H:i',
            'close_at' => 'sometimes|required|date_format:H:i|after:open_at',
        ]);

        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('hotels/thumbnails', 'public');
            $request['thumbnail'] = $thumbnailPath;
        }

        if ($request->has('images')) {
            $imagesPaths = [];
            foreach ($request->file('images') as $image) {
                $imagesPaths[] = $image->store('hotels/images', 'public');
            }
            $request['images'] = json_encode($imagesPaths);
        }

        $hotel->update($request->all());
        return response()->json($hotel);
    }

    public function destroy(Hotel $hotel)
    {
        $hotel->delete();
        return response()->json(['message' => 'Hotel deleted successfully'], 200);
    }
}
