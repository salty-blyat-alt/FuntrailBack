<?php

namespace App\Http\Controllers\Room;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    public function addRooms(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validate the incoming request data
            $validatedData = Validator::make($request->all(), [
                'room_type' => 'required|string|max:255',
                'price_per_night' => 'required|numeric|min:0',
            ]);

            if ($validatedData->fails()) {
                return $this->errorResponse(['errors' => $validatedData->errors()], 422);
            }

            // Fetch the hotel associated with the authenticated user
            $hotel = DB::table('hotels')->where('user_id', $request->user()->id)->first();

            if (!$hotel) {
                return $this->errorResponse('Hotel not found for this user.', 404);
            }

            // Prepare room data for insertion
            Room::create([
                'hotel_id' => $hotel->id,
                'room_type' => $request->input('room_type'), // Directly get the room_type from request
                'price_per_night' => $request->input('price_per_night'), // Directly get the price_per_night from request
                'status' => 'free',
            ]);

            DB::commit();

            return $this->successResponse('Rooms added successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack(); // Rollback on validation error
            return $this->errorResponse(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on general error
            return $this->errorResponse(['errors' => $e->getMessage()], 500);
        }
    }


    public function rooms(Request $request, $id)
    {
        // Set default date_start and date_end to today and tomorrow
        $date_start = $request->input('date_start', Carbon::today()->format('d/m/Y'));
        $date_end = $request->input('date_end', Carbon::tomorrow()->format('d/m/Y'));

        // Convert the provided dates to the Y-m-d format for querying
        $date_start = Carbon::createFromFormat('d/m/Y', $date_start)->format('Y-m-d');
        $date_end = Carbon::createFromFormat('d/m/Y', $date_end)->format('Y-m-d');

        // Base query to get all rooms for the hotel
        $rooms = Room::where('hotel_id', $id)->get();

        if ($date_start && $date_end) {
            $bookedRoomIds = Booking::where('hotel_id', $id)
                ->where(function ($query) use ($date_start, $date_end) {
                    $query->where(function ($q) use ($date_start, $date_end) {
                        $q->where('date_start', '<=', $date_end)
                            ->where('date_end', '>=', $date_start);
                    });
                })
                ->pluck('room_id'); // Get the room IDs that are booked

            // Filter out the booked rooms from the available rooms
            $availableRooms = $rooms->whereNotIn('id', $bookedRoomIds);
        } else {
            // If no date range is provided, all rooms are available
            $availableRooms = $rooms;
        }
        $availableRoomsArray = $availableRooms->values()->all(); // Reindexing the array
        // Return the available rooms
        return $this->successResponse($availableRoomsArray);
    }





    public function deleteRoom(Request $request)
    {
        // Fetch the hotel associated with the authenticated user
        $hotel = DB::table('hotels')->where('user_id', $request->user()->id)->first();

        if (!$hotel) {
            return $this->errorResponse('Hotel not found for this user.', 404);
        }

        DB::beginTransaction();

        try {
            // Validate the incoming request data
            $validatedData = Validator::make($request->all(), [
                'room_id' => 'required|string|max:255', // Ensure room_id is required
            ]);

            if ($validatedData->fails()) {
                return $this->errorResponse(['errors' => $validatedData->errors()], 422);
            }

            // Extract the room ID from the request
            $roomId = $request->input('room_id');

            // Check if the room exists in the hotel
            $existingRoom = DB::table('rooms')
                ->where('hotel_id', $hotel->id)
                ->where('id', $roomId)
                ->first(); // Fetch a single room 
            // If the room does not exist, return an error
            if (!$existingRoom) {
                return $this->errorResponse('Room not found in this hotel.', 404);
            }

            // Delete the existing room
            DB::table('rooms')
                ->where('id', $existingRoom->id)
                ->delete();

            DB::commit(); // Commit the transaction

            return $this->successResponse('Room deleted successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack(); // Rollback on validation error
            return $this->errorResponse(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on general error
            return $this->errorResponse(['errors' => $e->getMessage()], 500);
        }
    }
}
