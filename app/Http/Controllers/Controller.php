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
            'result' => false,
            'result_code' => $statusCode,
            'result_message' => $message,
            'body' => null,
        ], $statusCode);
    }

    public function registerRules()
    {
        return [
            'username'      => 'required|string',
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
            'name'              => 'required|string',
            'location'          => 'required|string',
            'total_rooms'       => 'required|integer',
            'bookings_count'    => 'required|integer',
            'average_rating'    => 'required|numeric|between:0,5',
            'revenue'           => 'required|numeric',
        ];
    }

    public function bookingRules()
    {
        return [
            'id'        => 'required|int',
            'room_id'   => 'required|int',
            'user_id'   => 'required|int',
            'check_in'  => 'required|datetime:H:i:s',
            'check_out' => 'required|datetime:H:i:s',
            'date'      => 'required|date_format:d:m:Y',
            'total'     => 'required|numeric',
        ];
    }

    public function roomRules()
    {
        return [
            'hotel_id'  => 'required|int',
            'room_type' => 'required|int',
            'user_id'   => 'required|int',
            'price'     => 'required|numeric',
            'status'    => 'required|string'
        ];
    }
    public function commissionRules()
    {
        return [
            'user_id'           => 'required|int',
            'commission_rate'   => 'required|int',
            'total_commision'   => 'required|numeric',
        ];
    }
}
