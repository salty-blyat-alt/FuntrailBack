<?php

namespace App\Http\Controllers\Hotel;

use App\Models\Hotel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
        $perPage = $request->query('per_page', 10);
        $province_id = $request->query('province_id', null);
        $min_price = $request->query('min_price', 0);
        $max_price = $request->query('max_price', PHP_INT_MAX);
        $name = $request->query('name', null);

        $hotels = DB::table('hotels as h')
            ->leftJoin('users as c', 'c.id', '=', 'h.user_id')
            ->leftJoin('rooms as r', 'r.hotel_id', '=', 'h.id')
            ->leftJoin('provinces as p', 'p.id', '=', 'h.province_id')
            ->select( 
                'c.username as owner',
                'p.name as province',
                'h.*', 
                DB::raw('CASE WHEN COUNT(r.id) > 0 THEN "available" ELSE "not available" END as is_available')
            );

        // Apply filters if provided
        if ($province_id) {
            $hotels->where('h.province_id', $province_id);
        }

        if ($min_price || $max_price < PHP_INT_MAX) {
            $hotels->whereBetween('r.price_per_night', [$min_price, $max_price]);
        }

        // if() {

        // }

        if ($name) {
            $hotels->where(DB::raw('LOWER(h.name)'), 'like', '%' . strtolower($name) . '%');
        }

        $hotels = $hotels
            ->groupBy('h.id', 'p.name', 'h.name', 'c.username', 'h.province_id', 'h.address', 'h.description', 'h.thumbnail', 'h.images', 'h.open_at', 'h.close_at')
            ->paginate($perPage);

        // Clean pagination if necessary
        $hotels = cleanPagination($hotels);

        return $this->successResponse($hotels);
    }


    // work done
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validatedData = Validator::make(array_merge(
                $request->all(),
                [
                    'user_id'       => $request->user()->id,
                    'province_id'   => $request->user()->province_id,
                    'phone_number'  => $request->user()->phone_number
                ]
            ), $this->hotelRules());

            if ($validatedData->fails()) {
                return $this->errorResponse('Hotel failed to create', 500);
            }

            // Check if user already owns a hotel before proceeding with creation
            $user = User::findOrFail($request->user()->id);
            $hotelExist = DB::table('hotels')->where('user_id', $user->id)->exists();

            if ($user->user_type === 'hotel' || $user->user_type === 'restaurant' || $hotelExist) {
                return $this->errorResponse('User already owns a hotel or restaurant.', 400);
            }

            // Handle file uploads
            $thumbnailPath = uploadDocument($request->file('thumbnail'), 'hotels/thumbnails');

            $imagesPaths = [];
            if ($request->hasfile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $imagesPaths[$index] = uploadDocument($file, 'hotels/images');
                }
            }

            // Validate and create hotel
            $imagesJson = json_encode($imagesPaths);
            $validatedData = $validatedData->validated();

            // Get optional fields from request, default to null if not provided
            $facilitiesJson = $request->facilities ? json_encode($request->facilities) : null;
            $policiesJson = $request->policies ? json_encode($request->policies) : null;

            Hotel::create([
                'name'         => $validatedData['name'],
                'user_id'      => $request->user()->id,
                'province_id'  => $request->user()->province_id,
                'address'      => $validatedData['address'],
                'description'  => $validatedData['description'] ?? null,
                'phone_number' => $request->user()->phone_number,
                'thumbnail'    => $thumbnailPath ?? null,
                'images'       => $imagesJson ?? null,
                'facilities'   => $facilitiesJson,  // New field
                'policies'     => $policiesJson,    // New field
                'open_at'      => $validatedData['open_at'],
                'close_at'     => $validatedData['close_at'],
            ]);

            // Update user type after hotel creation
            DB::table('users')->where('id', $request->user()->id)->update([
                'user_type' => 'hotel'
            ]);

            DB::commit();
            return $this->successResponse('Hotel created successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack(); // Rollback the transaction on validation error
            return $this->errorResponse(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction on general error
            return $this->errorResponse(['errors' => $e->getMessage()], 500);
        }
    }


    // work done
    public function show(string $id)
    {
        $hotel = Hotel::find($id);
        if (!$hotel) {
            info('Hotel failed to get');
            return $this->errorResponse('Hotel failed to get', 500);
        }
        $province = Province::where('id', $hotel->province_id)->value('name');
        $owner = User::where('id', $hotel->user_id)->value('username');
        $close_at =  Carbon::parse($hotel->open_at)->format('H:i');
        $open_at =  Carbon::parse($hotel->close_at)->format('H:i');

        $hotelData = [
            'id'            => $hotel->id,
            'name'          => $hotel->name,
            'owner'         => $owner,
            'province'      => $province,
            'address'       => $hotel->address,
            'description'   => $hotel->description,
            'thumbnail'     => $hotel->thumbnail,
            'images'        => $hotel->images,
            'facilities'    => $hotel->facilities,
            'policies'      => $hotel->policies,
            'open_at'      => $open_at,
            'close_at'     => $close_at,
        ];
        return $this->successResponse($hotelData);
    }


    // work done
    public function update(Request $request)
    {
        $user_id = $request->user()->id;

        $hotel = Hotel::where('user_id', $user_id)->first();

        if (!$hotel) {
            info('Hotel not found');
            return $this->errorResponse('Hotel failed to update');
        }

        if (!$request->hasAny(['name', 'province_id', 'address', 'description', 'thumbnail', 'images', 'facilities', 'policies', 'open_at', 'close_at'])) {
            return $this->errorResponse('Hotel failed to update');
        }

        // Get optional fields from request, default to null if not provided
        $facilitiesJson = $request->facilities ? json_encode($request->facilities) : null;
        $policiesJson = $request->policies ? json_encode($request->policies) : null;

        DB::table('hotels')->where('id', $hotel->id)->update([
            'name'         => $request->name          ?? $hotel->name,
            'user_id'      => $hotel->user_id,
            'province_id'  => $request->province_id   ?? $hotel->province_id,
            'address'      => $request->address       ?? $hotel->address,
            'description'  => $request->description   ?? $hotel->description,
            'thumbnail'    => $request->thumbnail     ?? $hotel->thumbnail,
            'images'       => $request->images        ?? $hotel->images,
            'facilities'   => $facilitiesJson         ?? $hotel->facilities, // New field
            'policies'     => $policiesJson           ?? $hotel->policies,   // New field
            'open_at'      => $request->open_at       ?? $hotel->open_at,
            'close_at'     => $request->close_at      ?? $hotel->close_at,
        ]);

        return $this->successResponse('Hotel updated successfully');
    }

    // work done
    public function destroy(Request $request)
    {
        $user_id = $request->user()->id;
        $hotel = Hotel::where('user_id', $user_id)->first();

        if (!$hotel) {
            return $this->errorResponse(['message' => 'Hotel not found.'], 404);
        }

        DB::beginTransaction();

        try {
            // Update user type to 'customer'
            User::where('id', $user_id)->update([
                'user_type' => 'customer'
            ]);

            // Delete the hotel
            $hotel->delete();

            // Commit the transaction
            DB::commit();

            return $this->successResponse('Hotel deleted successfully');
        } catch (\Exception $e) {
            // Rollback the transaction on failure
            DB::rollBack();
            return $this->errorResponse(['message' => 'Failed to delete hotel.'], 500);
        }
    }



    public function popular()
    {
        $hotelBookings = DB::table('bookings as b')
            ->leftJoin('users as u', 'u.id', '=', 'b.user_id')
            ->leftJoin('hotels as h', 'h.id', '=', 'b.hotel_id')
            ->select(
                'h.id as hotel_id',
                'h.name as hotel_name',
                'h.id',
                'h.user_id',
                'h.province_id',
                'h.address',
                'h.description',
                'h.thumbnail',
                'h.images',
                'h.open_at',
                'h.close_at',
                'h.created_at',
                'h.updated_at',
                'h.facilities',
                'h.policies',
                DB::raw('count(b.id) as popular_point')
            )
            ->groupBy(
                'h.id',
                'h.name',
                'h.user_id',
                'h.province_id',
                'h.address',
                'h.description',
                'h.thumbnail',
                'h.images',
                'h.open_at',
                'h.close_at',
                'h.created_at',
                'h.updated_at',
                'h.facilities',
                'h.policies'
            )
            ->orderByDesc('popular_point')
            ->get();

        return $this->successResponse($hotelBookings);
    }

    public function rooms($id)
    {
        $rooms = Room::where('hotel_id', $id)->get();

        return $this->successResponse($rooms);
    }
}
