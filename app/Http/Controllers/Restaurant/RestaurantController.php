<?php 
namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $restaurants = Restaurant::all();
        return response()->json($restaurants);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'province' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone_number' => 'nullable|string|max:15',
            'image' => 'nullable|url',
            'open_at' => 'required|date_format:H:i:s',
            'close_at' => 'required|date_format:H:i:s',
        ]);

        $restaurant = Restaurant::create($request->all());

        return response()->json($restaurant, 201);
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
