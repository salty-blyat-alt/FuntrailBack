<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), $this->registerRules());

        if ($validator->fails()) {
            info($validator->messages());
            return $this->errorResponse('User failed to register', 422);
        }

        try {
            $validatedData = $validator->validated();

            if ($request->hasFile('profile_img')) {
                // Store the image and get the path
                $imagePath = $request->file('profile_img')->store('users', 'public');
                $validatedData['profile_img'] = $imagePath;
            } else {
                $validatedData['profile_img'] = null;
            }


            $user = User::create([
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'email' => $validatedData['email'],
                'user_type' => $validatedData['user_type'],
                'province_id' => $validatedData['province_id'],
                'phone_number' => $validatedData['phone_number'],
                'profile_img' => $validatedData['profile_img'],
                'password' => Hash::make($validatedData['password'])
            ]);


            // Generate a Sanctum token
            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'message' => 'User created successfully',
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            // for production
            info('Error creating user: ' . $e->getMessage());
            return $this->errorResponse('User failed to create', 500);
        }
    }


    public function login(Request $request): JsonResponse
    {
        try {
            // Validate request data
            $request->validate(([
                'email'         => 'required|email',
                'password'         => 'required|string'
            ]));

            // Attempt to find the user by email
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->errorResponse('Email or password is not correct', 422);
            }


            $token = $user->createToken($user->name . 'Auth-Token')->plainTextToken;


            return $this->errorResponse([
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer'
             ], 200);
        } catch (\Exception $e) {
            // Log the error and return a generic error response
            Log::error('Error during login: ' . $e->getMessage());
            return $this->errorResponse(['message' => 'Login failed due to a server error'], 500);
        }
    }



    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'User logged out successfully'
        ], 200);
    }
}
