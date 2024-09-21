<?php

namespace Tests\Feature\Hotel;

use App\Http\Controllers\Hotel\HotelController;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HotelTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    // Retrieve a paginated list of hotels with default parameters
    public function test_retrieve_paginated_list_of_hotels_with_default_parameters()
    {
        // Arrange
        $request = new \Illuminate\Http\Request();
        $controller = new \App\Http\Controllers\Hotel\HotelController();

        // Act
        $response = $controller->index($request);
        $data = $response->getData(true); // Get the data as an associative array

        // Assert
        $this->assertEquals(200, $response->status());

        // Ensure 'body' is present in the response
        $this->assertArrayHasKey('body', $data);

        // Ensure 'items' is present inside the 'body'
        $this->assertArrayHasKey('items', $data['body']);

        // Ensure 'paginate' is present inside the 'body'
        $this->assertArrayHasKey('paginate', $data['body']);

        // Additional checks for items structure
        $this->assertNotEmpty($data['body']['items']);
        $this->assertArrayHasKey('id', $data['body']['items'][0]);
        $this->assertArrayHasKey('name', $data['body']['items'][0]);
        $this->assertArrayHasKey('thumbnail', $data['body']['items'][0]);
    }
    // Handle the case when no hotels match the filters
    public function test_handle_no_hotels_match_filters()
    {
        // Arrange: Create a new request and set the controller
        $request = new \Illuminate\Http\Request();
        $controller = new \App\Http\Controllers\Hotel\HotelController();

        // Define filters that will return no hotels
        $province_id = 999; // Assuming this province_id does not exist
        $min_price = 10000; // Assuming no hotel has this minimum price
        $max_price = 20000;
        $name = 'NonExistentHotelName';

        // Act: Call the controller's index method with filters
        $response = $controller->index($request, $province_id, $min_price, $max_price, $name);

        // Assert: Verify the response status is 200 (OK)
        $this->assertEquals(200, $response->status());

        // Assert: Check that the 'items' array inside 'body' is empty, meaning no hotels were found
        $data = $response->getData(true);
        $this->assertArrayHasKey('body', $data);
        $this->assertArrayHasKey('items', $data['body']);
        $this->assertEmpty($data['body']['items'], 'Expected no hotels, but some hotels were returned.');
    }

    // Successfully deletes a hotel when the user is the owner
    public function test_successful_hotel_deletion()
    {
        // Arrange: Create a user and a hotel related to the user
        $user = User::factory()->create([
            'user_type' => 'hotel',
            'phone_number' => '123-456-7890',
        ]);

        $hotel = Hotel::factory()->create(['user_id' => $user->id]);

        // Simulate a POST request to the hotel delete API endpoint
        $request = Request::create('/api/hotel/delete', 'POST', ['id' => $hotel->id]);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Mock the database transactions
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollBack')->never();

        // Act: Call the controller's delete method
        $controller = new HotelController();
        $response = $controller->destroy($request, $hotel->id);  // Pass hotel ID for deletion

        // Assert: Check the response structure and status
        $this->assertEquals(200, $response->status());
        $this->assertTrue($response->getData()->result); // Adjusted
        $this->assertEquals(200, $response->getData()->result_code);
        $this->assertEquals('Success', $response->getData()->result_message);
        $this->assertEquals('Hotel deleted successfully', $response->getData()->body);

        // Assert: Ensure the hotel is removed from the database
        $this->assertDatabaseMissing('hotels', ['id' => $hotel->id]);
    }
}
