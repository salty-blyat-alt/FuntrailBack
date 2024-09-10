<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

abstract class Controller
{
    use ValidatesRequests;

    protected function successResponse($data, $message = "Success", $statusCode = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'result'            => true,
            'result_code'       => $statusCode,
            'result_message'    => $message,
            'body'              => $data,
        ], 200);
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

    public function registerRules()
    {
        return [
            'username'      => 'required|string',
            'email'         => 'required|string|email|unique:users',
            'password'      => 'required|string|min:8',
            'province_id'   => 'nullable|integer',
            'phone_number'  => 'required|string',
            'profile_img'   => 'nullable|file|mimes:jpeg,png,jpg',
        ];
    }

    public function hotelRules()
    {
        return [
            'name'          => 'required|string',
            'user_id'       => 'required|int',
            'province_id'   => 'required|int',
            'address'       => 'required|string',
            'description'   => 'string',
            'thumbnail'     => 'nullable|file|mimes:jpeg,png,jpg',
            'images'        => 'nullable|array',
            'images.*'      => 'file|mimes:jpeg,png,jpg|max:2048',
            'open_at'       => 'required|string',
            'close_at'      => 'required|string',
        ];
    }

    public function bookingRules()
    {
        return [
            'room_ids'     => 'required|array', 
            'hotel_id'     => 'required|int',
            'room_ids.*'   => 'required|int', 
            'date_start'   => 'required|date_format:d/m/Y',
            'date_end'     => 'required|date_format:d/m/Y|after_or_equal:date_start',
             
        ];
    }

    public function roomRules()
    {
        return [
            'hotel_id'              => 'required|int',
            'room_type'             => 'required|int',
            'user_id'               => 'required|int',
            'price_per_night'       => 'required|numeric'
        ];
    }

    public function commissionRules()
    {
        return [
            'user_id'            => 'required|integer|exists:users,id',
            'payment_type'       => 'required|string',
            'total_payment'      => 'required|numeric|min:0',
            'commission_rate'    => 'required|integer|min:0|max:100',
            'total_commision'    => 'required|numeric|min:0',
        ];
    }
}
