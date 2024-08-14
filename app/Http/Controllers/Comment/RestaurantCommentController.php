<?php

namespace App\Http\Controllers\Comment;

use App\Http\Controllers\Controller;
use App\Models\RestaurantComment;
use Illuminate\Http\Request;

class RestaurantCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $comments = RestaurantComment::all();
        return response()->json($comments);
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'context' => 'required|string',
            'star' => 'required|integer|min:1|max:5', // Assuming star rating is between 1 and 5
            'user_id' => 'required|exists:users,id',
            'restaurant_id' => 'required|exists:restaurants,id',
        ]);
    
        $comment = RestaurantComment::create($request->all());
        return response()->json($comment, 201);
    }
    

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $comment = RestaurantComment::findOrFail($id);
        return response()->json($comment);
    }
    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'context' => 'sometimes|required|string',
            'star' => 'sometimes|required|integer|min:1|max:5',
            'user_id' => 'sometimes|required|exists:users,id',
            'restaurant_id' => 'sometimes|required|exists:restaurants,id',
        ]);
    
        $comment = RestaurantComment::findOrFail($id);
        $comment->update($request->all());
        return response()->json($comment);
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $comment = RestaurantComment::findOrFail($id);
        $comment->delete();
        return response()->json(null, 204);
    }
    
}
