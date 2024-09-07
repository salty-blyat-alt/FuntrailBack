<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that users can register successfully.
     */
    public function test_users_can_register(): void
    {
        $response = $this->postJson('api/auth/register', [
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'user_type' => 'customer',
            'province_id' => 1,
            'phone_number' => '1234567890',
            'profile_img' => null
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'result',
                'result_code',
                'result_message',
                'body' => [
                    "message",
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'username' => 'testuser',
            'email' => 'testuser@example.com',
        ]);
    }

    /**
     * Test that registration fails with invalid data.
     */
    public function test_registration_fails_with_invalid_data(): void
    {
        $uniqueEmail = 'invalid_' . time() . '@example.com'; // Generate a unique email

        $response = $this->postJson('api/auth/register', [
            'username' => '',
            'email' => $uniqueEmail,
            'phone_numer' => '549809278',
            'password' => 'short',
            'user_type' => '',
            'phone_number' => '54',
        ]);
        $response->assertStatus(422)
            ->assertJson([
                "result" => false,
                "result_code" => 422,
                "result_message" => "User failed to register",
                "body" => null
            ]);
    }


    /**
     * Test that users can authenticate using login.
     */
    public function test_users_can_authenticate_using_login(): void
    {
        $uniqueEmail = 'testuser_' . time() . '@example.com'; // Generate a unique email

        $user = User::factory()->create([
            'username' => 'testuser',
            'email' => $uniqueEmail, 
            'password' => bcrypt('password123'),
            'user_type' => 'customer',
            'province_id' => 1,
            'phone_number' => '1234567890',
            'profile_img' => null
        ]);

        $response = $this->postJson('api/auth/login', [
            'username' => 'testuser',
            'email' => $uniqueEmail, 
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'result',
                'result_code',
                'result_message',
                'body' => [
                    'user',
                    'access_token',
                    'token_type',
                ],
            ]);
    }


    /**
     * Test that users cannot authenticate with an invalid password.
     */
    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'phone_number' => '213434',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('api/auth/login', [
            'username' => $user->username,
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'result',
                'result_code',
                'result_message',
                'body',
            ]);

        $this->assertGuest();
    }

    /**
     * Test that users can log out successfully.
     */
    public function test_users_can_logout(): void
    {
        $user = User::factory()->create([
            'username' => 'test',
            "email" => 'heng@gmail.com',
            'password' => bcrypt('11112222'),
            'phone_number' => '1234567890',
        ]);

        // Log in the user to generate a token
        $loginResponse = $this->postJson('api/auth/login', [
            'username' => 'test',
            'email' => 'heng@gmail.com',
            'password' => '11112222',
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('body.access_token');

        // Log out the user using the token
        $logoutResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('api/auth/logout');

        $logoutResponse->assertStatus(200)
            ->assertJsonStructure([
                'result',
                'result_code',
                'result_message',
                'body',
            ]);

        // Assert the user is logged out
        $this->assertGuest('api');
    }

    /**
     * Test that unauthenticated users cannot log out.
     */
    public function test_unauthenticated_users_cannot_logout(): void
    {
        $response = $this->postJson('api/auth/logout');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}
