<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\Province;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            // 'password' => 'required|string|min:8',        => for proudction
            'password' => 'required|string',
            'user_type' => 'sometimes|in:customer,restaurant,hotel',
            'province' => 'nullable|string|max:255',
            'balance' => 'sometimes|numeric',
            'phone_number' => 'nullable|string|max:15',
            'profile_img' => 'nullable|string',
        ]);

        // Hash the password before saving
        $validatedData['password'] = Hash::make($validatedData['password']);

        // Create the user
        $user = User::create($validatedData);

        return response()->json($user, 201); // 201 status code for resource created
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Validate the request data
        $validatedData = $request->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:8',
            'user_type' => 'sometimes|required|string|max:50',
            'province' => 'nullable|string|max:255',
            'balance' => 'nullable|numeric',
            'phone_number' => 'nullable|string|max:15',
            'profile_img' => 'nullable|string',
        ]);

        // If password is provided, hash it before saving
        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        // Update the user
        $user->update($validatedData);

        return response()->json($user);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(null, 204); // 204 status code for no content
    }
    
    public function profile(Request $request) {
        // Check if user is authenticated
        if ($request->user()) {
            $user = $request->user();
            $province = Province::where('id', $user->province_id)->value('name');            
            $user = [
                "id"=> $user->id,
                "username"=> $user->username,
                "email"=> $user->email,
                "balance"=> $user->balance, 
                "user_type"=> $user->user_type,
                "province"=> $province,
                "phone_number"=> $user->phone_number,
                "profile_img"=> $user->profile_img
            ];
                return $this->successResponse($user);
        }
    
        return $this->errorResponse(['error' => 'Unauthorized'], 401);
    }


}
