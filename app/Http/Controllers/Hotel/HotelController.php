<?php

namespace App\Http\Controllers\Hotel;

use App\Models\Hotel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Province;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HotelController extends Controller
{
    // work done
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);

        $hotels = Hotel::select(
            'hotels.id',
            'hotels.name',
            'hotels.address',
            'hotels.description',
            'hotels.thumbnail',
            'hotels.images',
            'hotels.open_at',
            'hotels.close_at',
            'provinces.name as province',
            'users.username as owner' // Select the owner's name
        )
            ->leftJoin('provinces', 'provinces.id', '=', 'hotels.province_id')
            ->leftJoin('users', 'users.id', '=', 'hotels.user_id')
            ->paginate($perPage);

        $hotels = cleanPagination($hotels);

        return $this->successResponse($hotels);
    }

    // work done
    public function store(Request $request)
    {
        $validatedData = Validator::make(array_merge(
            $request->all(),
            [
                'user_id'       => $request->user()->id,
                'province_id'   => $request->user()->province_id,
                'phone_number' => $request->user()->phone_number
            ]
        ), $this->hotelRules());

        if ($validatedData->fails()) {
            info($validatedData->messages());
            return $this->errorResponse("Hotel failed to create", 500);
        }

        $thumbnailPath = uploadDocument($request->file('thumbnail'), 'hotels/thumbnails');

        if ($request->hasfile('images')) {
            $imagesPaths = [];
            foreach ($request->file('images') as $index => $file) {
                $imagesPaths[$index]        = uploadDocument($file, 'hotels/images');
            }
        }

        $imagesJson = json_encode($imagesPaths);

        $validatedData = $validatedData->validated();

        $hotel = Hotel::create([
            'name'         => $request->name,
            'user_id'      => $request->user()->id,
            'province_id'  => $request->user()->province_id,
            'address'      => $request->address,
            'description'  => $request->description ?? null,
            'phone_number' => $request->user()->phone_number,
            'thumbnail'    => $thumbnailPath ?? null,
            'images'       => $imagesJson ?? null,
            'open_at'      => $request->open_at,
            'close_at'     => $request->close_at,
        ]);
        DB::table('users')->where('id', $request->user()->id)->update([
            'user_type' => 'hotel'
        ]);

        // for production
        // return $this->successResponse("Hotel created successfully");

        // for dev
        return $this->successResponse($hotel, 201);
    }

    // work done
    public function show(string $id)
    {
        $hotel = Hotel::find($id);
        $province = Province::where('id', $hotel->province_id)->value('name');
        $owner = User::where('id', $hotel->user_id)->value('username');
        $close_at =  Carbon::parse($hotel->open_at)->format('H:i');
        $open_at =  Carbon::parse($hotel->close_at)->format('H:i');


        $hotel = [
            "name"        => $hotel->name,
            "owner"       => $owner,
            "province"    => $province,
            "address"     => $hotel->address,
            "description" => $hotel->description,
            "thumbnail"   => $hotel->thumbnail,
            "images"      => $hotel->images,
            "open_at"     => $open_at,
            "close_at"    => $close_at,
            "created_at"  => $hotel->created_at,
            "updated_at"  => $hotel->updated_at,
        ];

        if (!$hotel) {
            info('Hotel failed to get');
            return $this->errorResponse('Hotel failed to get', 500);
        }

        return $this->successResponse($hotel);
    }


    // working
    public function update(Request $request)
    {
        $user_id = $request->user()->id;

        $hotel = Hotel::where('user_id', $user_id)->first();

        if (!$hotel) {
            info('Hotel not found');
            return $this->errorResponse('Hotel failed to update');
        }

        if (!$request->hasAny(['name', 'province_id', 'address', 'description', 'thumbnail', 'images', 'open_at', 'close_at'])) {
            return $this->errorResponse('Hotel failed to update');
        }


        DB::table('hotels')->where('id', $hotel->id)->update([
            'name'         => $request->name          ?? $hotel->name,
            'user_id'      => $request->user_id       ?? $hotel->user_id,
            'province_id'  => $request->province_id   ?? $hotel->province_id,
            'address'      => $request->address       ?? $hotel->address,
            'description'  => $request->description   ?? $hotel->description,
            'thumbnail'    => $request->thumbnail     ?? $hotel->thumbnail,
            'images'       => $request->images        ?? $hotel->images,
            'open_at'      => $request->open_at       ?? $hotel->open_at,
            'close_at'     => $request->close_at      ?? $hotel->close_at,
        ]);

        return $this->successResponse('Hotel updated successfully');
    }

    public function destroy(Request $request)
    {
        $user_id = $request->user()->id;
        $hotel = Hotel::where('user_id', $user_id)->first();

        if (!$hotel) {
            return $this->errorResponse(['message' => 'Hotel not found.'], 404);
        }
        $hotel->delete();
        return $this->successResponse('Hotel deleted successfully');
    }


    public function book(Request $request)
    {
        $validatedData = Validator::make($request->all(), $this->bookingRules());

        if ($validatedData->fails()) {
            info($validatedData->messages());
            return $this->errorResponse($validatedData->messages(), 500);
        }

        $customer_id = $request->user()->id;
        $customer_balance = $request->user()->balance;
        $date_start = Carbon::createFromFormat('d/m/Y', $request->date_start)->format('Y-m-d');
        $date_end = Carbon::createFromFormat('d/m/Y', $request->date_end)->format('Y-m-d');

        // Check room availability
        foreach ($request->room_ids as $room_id) {
            $room = Room::find($room_id);

            if ($room) {
                // Check if the room is available for the specified dates
                $isBooked = Booking::where('room_id', $room_id)
                    ->where(function ($query) use ($date_start, $date_end) {
                        $query->whereBetween('date_start', [$date_start, $date_end])
                            ->orWhereBetween('date_end', [$date_start, $date_end])
                            ->orWhere(function ($query) use ($date_start, $date_end) {
                                $query->where('date_start', '<=', $date_start)
                                    ->where('date_end', '>=', $date_end);
                            });
                    })
                    ->exists();

                if ($isBooked) {
                    return $this->errorResponse('Room ' . $room_id . ' is not available for the selected dates.', 400);
                }
            }
        }

        $user_type = $request->user()->user_type;
        $hotel_exist = Hotel::where('user_id', $customer_id)->exists();

        // Check if the user is the owner of the hotel
        if ($user_type === 'hotel' && $hotel_exist) {
            // Directly create bookings without a transaction for owners
            $bookings = [];

            foreach ($request->room_ids as $room_id) {
                $room = Room::find($room_id);

                if ($room) {
                    $booking = Booking::create([
                        'id' => rand(1, 4000000000), // Generate a new ID for each booking
                        'room_id' => $room_id,
                        'user_id' => $customer_id,
                        'date_start' => $date_start,
                        'date_end' => $date_end,
                        'total' => $room->price_per_night,
                    ]);

                    $bookings[] = $booking;
                }
            }

            return $this->successResponse(['message' => "Rooms successfully booked.", 'bookings' => $bookings], 200);
        }

        $total_cost = 0;
        $bookings = [];

        DB::beginTransaction();

        try {
            foreach ($request->room_ids as $room_id) {
                $room = Room::find($room_id);

                if ($room) {
                    $total_cost += $room->price_per_night;

                    $booking = Booking::create([
                        'id' => rand(1, 4000000000),
                        'room_id' => $room_id,
                        'user_id' => $customer_id,
                        'date_start' => $date_start,
                        'date_end' => $date_end,
                        'total' => $room->price_per_night,
                    ]);

                    $bookings[] = $booking;
                }
            }

            info("Total Cost: " . $total_cost);
            info("Customer Balance: " . $customer_balance);

            if ($total_cost > $customer_balance) {
                DB::rollBack();
                return $this->errorResponse('Insufficient balance', 400);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(['message' => 'An error occurred while processing your request.'], 500);
        }

        return $this->successResponse(['message' => "Rooms successfully booked.", 'bookings' => $bookings], 200);
    }
}
