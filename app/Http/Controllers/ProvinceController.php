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
        return $this->successResponse($provinces);
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
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'string|nullable', // Name is optional
            'img' => 'nullable|image|mimes:jpeg,png,jpg,gif', // Optional image upload with specific formats
        ]);

        // Find the province by ID
        $province = Province::findOrFail($id);

        // Handle image upload if provided
        if ($request->hasFile('img')) {
            // Assuming uploadDocument is a custom function to handle file uploads
            $imgPath = uploadDocument($request->file('img'), 'provinces');

            // Update the province's img path if the upload was successful
            $province->img = $imgPath;
        }

        if (isset($validatedData['name'])) {
            $province->name = $validatedData['name'];
        }

        // Save the updated province
        $province->save();


        // Return a response (optional)
        return response()->json(['message' => 'Province updated successfully.', 'province' => $province], 200);
    }
}
