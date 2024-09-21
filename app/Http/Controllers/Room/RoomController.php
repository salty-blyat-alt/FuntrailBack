<?php

namespace App\Http\Controllers\Room;

use App\Http\Controllers\Controller;
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
                'rooms' => 'required|array',
                'rooms.*.room_type' => 'required|string|max:255',
                'rooms.*.price_per_night' => 'required|numeric|min:0',
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
            $roomsData = [];
            foreach ($request->rooms as $room) {
                $roomsData[] = [
                    'hotel_id' => $hotel->id,
                    'room_type' => $room['room_type'],
                    'price_per_night' => $room['price_per_night'],
                    'status' => 'free', // Default status
                ];
            }

            // Insert multiple rooms
            DB::table('rooms')->insert($roomsData);

            DB::commit(); // Commit the transaction

            return $this->successResponse('Rooms added successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack(); // Rollback on validation error
            return $this->errorResponse(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on general error
            return $this->errorResponse(['errors' => $e->getMessage()], 500);
        }
    }


    public function deleteRooms(Request $request)
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
                'rooms' => 'required|array',
                'rooms.*.room_id' => 'required|string|max:255',
            ]);

            if ($validatedData->fails()) {
                return $this->errorResponse(['errors' => $validatedData->errors()], 422);
            }

            // Extract room IDs from the request
            $roomIds = collect($request->input('rooms'))->pluck('room_id')->toArray();

            // Check for existing rooms in the hotel
            $existingRooms = DB::table('rooms')
                ->where('hotel_id', $hotel->id)
                ->whereIn('id', $roomIds)
                ->pluck('id')
                ->toArray();

            // If not all room IDs exist, return an error
            if (count($existingRooms) !== count($roomIds)) {
                return $this->errorResponse('Some room IDs do not exist.', 404);
            }

            // Delete only the existing rooms
            DB::table('rooms')
                ->whereIn('id', $existingRooms)
                ->delete();

            DB::commit(); // Commit the transaction

            return $this->successResponse('Rooms deleted successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack(); // Rollback on validation error
            return $this->errorResponse(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on general error
            return $this->errorResponse(['errors' => $e->getMessage()], 500);
        }
    }
}
