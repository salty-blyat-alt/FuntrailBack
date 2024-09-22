<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

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
                $imgPath = uploadDocument($request->file('profile_img'), 'users/profiles');
            }

            $user = User::create([
                'username' => $validatedData['username'],
                'email' => $validatedData['email'],
                'user_type' => 'customer',
                'province_id' => $validatedData['province_id'],
                'phone_number' => $validatedData['phone_number'],
                'profile_img' => $imgPath ?? null,
                'password' => Hash::make($validatedData['password'])
            ]);

            $token = $user->createToken($user->username)->plainTextToken;

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
            $request->validate([
                'email' => 'required|email|exists:users,email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->errorResponse('Email or password is not correct', 422);
            }

            $token = $user->createToken($user->username)->plainTextToken;

            return $this->successResponse([
                'access_token' => $token,
                'token_type' => 'Bearer'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse($e->errors(), 422);
        } catch (\Exception $e) {

            Log::error('Error during login: ' . $e->getMessage());
            return $this->errorResponse('An error occurred during login. Please try again later.', 500);
        }
    }


    public function logout(Request $request)
    {
        $user = $request->user()->tokens()->delete();
        if (!$user) {
            return $this->errorResponse('Unauthicated', 401);
        }
        return $this->successResponse(['message' => 'User logged out successfully'], 200);
    }


    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Check if the reset link was sent
        if ($status === Password::RESET_LINK_SENT) {
            return $this->successResponse([
                'message' => 'Password reset link sent to your email.',
                'status' => __($status),
            ], 200);
        } else {
            // If the link could not be sent
            return $this->errorResponse([
                'message' => 'Failed to send password reset link.',
                'status' => __($status),
            ], 500);
        }
    } 

    public function resetPassword(Request $request): JsonResponse
    {
        // Validate the request inputs
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Attempt to reset the password
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                // Update the user's password
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                // Save the changes
                $user->save();

                // Fire the password reset event
                event(new PasswordReset($user));
            }
        );

        // Check the status and return appropriate response
        if ($status === Password::PASSWORD_RESET) {
            return $this->successResponse([
                'message' => 'Password has been successfully reset.',
                'status' => __($status),
            ], 200);
        } else {
            return $this->errorResponse([
                'message' => 'Failed to reset the password.',
                'status' => __($status),
            ], 500);
        }
    }
}
