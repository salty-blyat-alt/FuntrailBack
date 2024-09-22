<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$types)
    {
        $user = Auth::user();

        if ($user && in_array($user->user_type, $types)) {
            return $next($request);
        }

        return $this->errorResponse('Unauthorized', 403);
    }

    protected function errorResponse($message = "Error", $statusCode = 422): JsonResponse
    {
        return response()->json([
            'result'            => false,
            'result_code'       => $statusCode,
            'result_message'    => $message,
            'body'              => null,
        ], $statusCode);
    }
}
