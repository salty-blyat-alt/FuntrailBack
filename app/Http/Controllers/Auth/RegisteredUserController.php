<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;

class RegisteredUserController extends Controller
{
    protected $userController;

    /**
     * Create a new controller instance.
     *
     * @param \App\Http\Controllers\UserController $userController
     */
    public function __construct(UserController $userController)
    {
        $this->userController = $userController;
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response
    {
        // Validate the request
        $fields = $request->validate([
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|max:255|unique:users,email',
            'password' => 'required|confirmed|max:255',
        ]);

        // Prepare the data for user creation
        $requestData = [
            'first_name' => $fields['first_name'],
            'last_name' => $fields['last_name'],
            'email' => $fields['email'],
            'password' => $fields['password'],
        ];

        // Call the UserController's store method to create the user
        $user = $this->userController->store(new Request($requestData));

        // Generate a token for the user
        $token = $user->createToken($request->first_name);

        return response()->json(['token' => $token->plainTextToken, 'user' => $user]);
    }
}
