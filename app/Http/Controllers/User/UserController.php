<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\Province;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);


        $users = User::paginate($perPage);
        $users = cleanPagination($users);
        return $this->successResponse($users);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate($this->userRules());

        // Hash the password before saving
        $validatedData['password'] = Hash::make($validatedData['password']);
 
        // Create the user
        User::create([
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'password' => $validatedData['password'],
            'user_type' => $validatedData['user_type'],
            'province_id' => $validatedData['province_id'],
            'balance' => $validatedData['balance'],
            'phone_number' => $validatedData['phone_number'],
            'profile_img' => $validatedData['profile_img']?? null, 
        ]);

        return $this->successResponse('User created successfully', 201);
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        if (!$user) {
            return $this->errorResponse($user);
        }

        return $this->successResponse($user);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);  
        if(!$user){
            return $this->errorResponse('User not found', 404);
        }

        if (!$request->hasAny(['username', 'email', 'password', 'profile_img','user_type', 'phone_number', 'province_id'])) {
            return $this->errorResponse('User failed to update');
        }
        
        // If password is provided, hash it before saving 
        if (isset($request->password)) {
            $request->password = Hash::make($request->password);
        }
        // Validate the request data
        DB::table('users')->where('id', $user->id)->update([
            'username'          => $request->username ?? $user->username,
            'email'             => $request->email ?? $user->email,
            'password'          => $request->password ?? $user->password,
            'user_type'         => $request->user_type ?? $user->user_type,
            'province_id'       => $request->province_id ?? $user->province_id,
            'balance'           => $request->balance ?? $user->balance,
            'phone_number'      => $request->phone_number ?? $user->phone_number,
            'profile_img'       => $request->profile_img ?? $user->profile_img,
        ]); 
        
        return $this->successResponse($user);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request)
    {
        $user = User::findOrFail($request->id);
        
        if(!$user) {
            return $this->errorResponse("User not found", 404);
        }

        $user->delete();

        return $this->successResponse('Delete user successfully', 204);
    }

    public function profile(Request $request)
    {
        if (!$request->user()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        // Check if user is authenticated
        $user = $request->user();
        $province = Province::where('id', $user->province_id)->value('name');
        $user = [
            "id" => $user->id,
            "username" => $user->username,
            "email" => $user->email,
            "balance" => $user->balance,
            "user_type" => $user->user_type,
            "province" => $province,
            "phone_number" => $user->phone_number,
            "profile_img" => $user->profile_img
        ];
        return $this->successResponse($user);
    }
}
