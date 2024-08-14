<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roomTypes = RoomType::all();
        return response()->json($roomTypes);
    }

       /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'room_type' => 'required|string|max:255',
            'image' => 'nullable|url', // or 'nullable|string' if it's a path
            'price' => 'required|numeric|min:0',
        ]);

        $roomType = RoomType::create($request->all());
        return response()->json($roomType, 201);
    }


      /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $roomType = RoomType::findOrFail($id);
        return response()->json($roomType);
    }


       /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'room_type' => 'sometimes|required|string|max:255',
            'image' => 'nullable|url', // or 'nullable|string' if it's a path
            'price' => 'sometimes|required|numeric|min:0',
        ]);

        $roomType = RoomType::findOrFail($id);
        $roomType->update($request->all());
        return response()->json($roomType);
    }


       /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $roomType = RoomType::findOrFail($id);
        $roomType->delete();
        return response()->json(null, 204);
    }
}