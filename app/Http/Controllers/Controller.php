<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

abstract class Controller
{
    use ValidatesRequests;

    protected function successResponse($data, $message = "success", $statusCode = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'result' => true,
            'result_code' => $statusCode,
            'result_message' => $message,
            'body' => $data,
        ], 200);
    }

    protected function errorResponse($message, $statusCode): JsonResponse
    {
        return response()->json([
            'result'        => false,
            'result_code'   => $statusCode,
            'result_message' => $message,
            'body' => null,
        ], 200);
    }

    public function registerRules()
    {
        return [
            'username'    => 'required|string',
            'email'         => 'required|string|email|unique:users',
            'password'      => 'required|string|min:8',
            'user_type'     => 'required|string',
            'province_id'   => 'nullable|integer',
            'phone_number'  => 'required|string',
            'profile_img'   => 'nullable|file|mimes:jpeg,png,jpg',
        ];
    }


    public function hotelRules()
    {
        return [
            'name' => 'required|string',
            'location' => 'required|string',
            'total_rooms' => 'required|integer',
            'bookings_count' => 'required|integer',
            'average_rating' => 'required|numeric|between:0,5',
            'revenue' => 'required|numeric',
        ];
    }
}
