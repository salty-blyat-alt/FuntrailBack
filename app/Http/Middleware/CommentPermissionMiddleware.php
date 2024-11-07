<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CommentPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        $comments = $request->get('comments');
        if ($comments) {
            $comments->transform(function ($comment) use ($user) {
                $isCommenter = $user ? $user->id === $comment->user_id : false;
                $isHotelOwner = $user ? $comment->user_id === $user->id : false;
                $comment->can_edit = $isCommenter;
                $comment->can_delete = $isCommenter || $isHotelOwner;

                if ($comment->replies) {
                    $comment->replies->transform(function ($reply) use ($user) {
                        $isCommenter = $user ? $user->id === $reply->user_id : false;
                        $isHotelOwner = $user ? $reply->user_id === $user->id : false;
                        $reply->can_delete = $isCommenter || $isHotelOwner;
                        $reply->can_edit = $isCommenter;
                        return $reply;
                    });
                }

                return $comment;
            });
        }

        return $next($request);
    }
}
