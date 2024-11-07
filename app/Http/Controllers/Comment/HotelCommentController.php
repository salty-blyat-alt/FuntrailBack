<?php

namespace App\Http\Controllers\Comment;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelComment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HotelCommentController extends Controller
{
    public function store(Request $request): JsonResponse
    {

        $user_id = Auth::id();

        $validator = Validator::make($request->all(), [
            'context' => 'required|string|max:255',
            'star' => 'nullable|integer|min:1|max:5',
            'hotel_id' => 'required|exists:hotels,id',
            'parent_id' => 'nullable|exists:hotel_comments,id', // Allow replies to reference existing comments
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        $comment = HotelComment::create([
            'context'       => $request->context,
            'star'          => $request->star,
            'hotel_id'      => $request->hotel_id,
            'parent_id'     => $request->parent_id,
            'user_id'       => $user_id,
        ]);

        return $this->successResponse($comment, "Comment created successfully", 201);
    }

    public function show(Request $request, int $hotel_id): JsonResponse
    {
        $perPage = $request->query('per_page', 5);
        $user_id = (int)$request->query('user_id');


        // Retrieve comments for the specified hotel, including their replies
        $comments = HotelComment::where('hotel_id', $hotel_id)
            ->orderBy('created_at', 'desc') // Order by most recent
            ->where('parent_id', null) // To only get top-level comments
            ->with(['user:id,username,profile_img', 'replies' => function ($query) {
                $query->with('user:id,username,profile_img'); // Load replies with their respective users
            }])
            ->paginate($perPage);

        if ($comments->isEmpty()) {
            return $this->errorResponse("No comments found for this hotel", 404);
        }

        // Format the created_at timestamp for each comment and move user fields to the same level
        $comments->getCollection()->transform(function ($comment) use ($user_id) {
            $hotel = Hotel::where('id', $comment->hotel_id)->first();
            $isCommenter = $user_id === $comment->user_id;
            $isHotelOwner = $hotel->user_id === $user_id; // Assuming the hotel owner is the same as the user who posted the comment
            // Format the base comment with additional permissions
            $comment->commented_at = $comment->created_at->diffForHumans([
                'parts' => 1,
                'join' => ', ',
                'short' => true,
            ]);
            $comment->username = $comment->user->username ?? 'Anonymous';
            $comment->profile_img = $comment->user->profile_img;
            $comment->can_edit = $isCommenter;
            $comment->can_delete = $isCommenter || $isHotelOwner;
            unset($comment->user);
            // Process replies if they exist
            if ($comment->replies) {
                $comment->replies->transform(function ($reply) use ($user_id, $hotel) {
                    $isCommenter = $user_id === $reply->user_id;
                    $isHotelOwner = $hotel->user_id === $user_id;
                    $reply->commented_at = $reply->created_at->diffForHumans();
                    $reply->username = $reply->user->username ?? 'Anonymous';
                    $reply->profile_img = $reply->user->profile_img;
                    $reply->can_delete = $isCommenter || $isHotelOwner;
                    $reply->can_edit = $isCommenter;
                    unset($reply->user);
                    return $reply;
                });
            }
            return $comment;
        });
        $comments = cleanPagination($comments);
        return $this->successResponse($comments, "Comments retrieved successfully");
    }


    /**
     * Update the specified hotel comment in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $context = $request->input('context');
        $comment = HotelComment::find($id);
        $user = Auth::user();


        if (!$comment) {
            return $this->errorResponse("Comment not found", 404);
        }

        // Check if the authenticated user is the hotel owner or the comment author

        $isCommenter = $user->id === $comment->user_id;

        // If neither the hotel owner nor the commenter, return unauthorized
        if (!$isCommenter) {
            return $this->errorResponse("Unauthorized", 403);
        }


        $validator = Validator::make($request->all(), [
            'context' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        // Update only the 'context' field
        $comment->context = $context;
        $comment->save(); // Save the changes

        return $this->successResponse("Comment updated successfully");
    }


    public function recent()
    {
        $comments = HotelComment::leftJoin('users', 'hotel_comments.user_id', '=', 'users.id')
            ->orderBy('hotel_comments.star', 'desc')
            ->orderBy('hotel_comments.created_at', 'desc')
            ->select('hotel_comments.*', 'users.username', 'users.profile_img') // Specify the columns to select, including username
            ->paginate(5);

        $comments->getCollection()->transform(function ($comment) {
            $comment->commented_at = $comment->created_at->diffForHumans([
                'parts' => 1,
                'join' => ', ',
                'short' => true,
            ]);
            return $comment; // Ensure the modified comment is returned
        });

        $comments = cleanPagination($comments);
        return $this->successResponse($comments);
    }

    public function destroy(Request $request): JsonResponse
    {
        // Fetch the authenticated user
        $user = Auth::user();

        // Retrieve the comment ID from the request's form data
        $id = $request->input('id');

        // Find the comment
        $comment = HotelComment::find($id);

        if (!$comment) {
            return $this->errorResponse("Comment not found", 404);
        }

        // Find the hotel owned by the user
        $hotel = Hotel::where('id', $comment->hotel_id)->first();

        // Check if the authenticated user is the hotel owner or the comment author
        $isHotelOwner = $hotel ? $hotel->user_id === $user->id : false;
        $isCommenter = $user->id === $comment->user_id;

        // If neither the hotel owner nor the commenter, return unauthorized
        if (!$isHotelOwner && !$isCommenter) {
            return $this->errorResponse("Unauthorized", 403);
        }

        // Delete all replies (child comments) where parent_id is the comment id
        HotelComment::where('parent_id', $id)->delete();

        // Delete the parent comment
        $comment->delete();

        return $this->successResponse("Comment and its replies deleted successfully");
    }
}
