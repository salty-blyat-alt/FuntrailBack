<?php

namespace App\Http\Controllers\Comment;

use App\Http\Controllers\Controller;
use App\Models\HotelComment;
use Illuminate\Http\Request;

class HotelCommentController extends Controller
{
   /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $comments = HotelComment::all();
        return response()->json($comments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'context' => 'required|string',
            'star' => 'required|integer|min:1|max:5',
            'user_id' => 'required|integer|exists:users,id',
            'hotel_id' => 'required|integer|exists:hotels,id',
        ]);

        $comment = HotelComment::create($request->all());

        return response()->json($comment, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $comment = HotelComment::findOrFail($id);
        return response()->json($comment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'context' => 'required|string',
            'star' => 'required|integer|min:1|max:5',
            'user_id' => 'required|integer|exists:users,id',
            'hotel_id' => 'required|integer|exists:hotels,id',
        ]);

        $comment = HotelComment::findOrFail($id);
        $comment->update($request->all());

        return response()->json($comment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $comment = HotelComment::findOrFail($id);
        $comment->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
