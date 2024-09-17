<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Models\Restaurant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RestaurantController extends Controller
{ 
    public function index(Request $request, $province_id = null, $min_price = 0, $max_price = PHP_INT_MAX, $name = null)
    {
        $perPage = $request->query('per_page', 15);
        $restaurants = DB::table('restaurants as r')
            ->leftJoin('users as c', 'c.id', '=', 'r.user_id')
            ->leftJoin('provinces as p', 'p.id', '=', 'r.province_id')
            ->select(
                'r.id as id',
                'r.name as name',
                'c.username as owner',
                'p.name as province',
                'r.address as address',
                'r.description as description',
                'r.image as thumbnail',
                'r.open_at as open_at',
                'r.close_at as close_at',
            );

        // Apply filters if provided
        if ($province_id) {
            $restaurants->where('r.province_id', $province_id);
        }

        // if ($min_price || $max_price < PHP_INT_MAX) {
        //     $hotels->whereBetween('r.price_per_night', [$min_price, $max_price]);
        // }

        if ($name) {
            $restaurants->where(DB::raw('LOWER(r.name)'), 'like', '%' . strtolower($name) . '%');
        }

        $restaurants = $restaurants
            ->groupBy('r.id', 'p.name', 'r.name', 'c.username', 'r.province_id', 'r.address', 'r.description', 'r.image', 'r.open_at', 'r.close_at')
            ->paginate($perPage);

        // Clean pagination if necessary
        $restaurants = cleanPagination($restaurants);

        return $this->successResponse($restaurants);
    }

    public function store(Request $request)
    {


        try {
            $data = array_merge($request->all(), [
                'user_id'       => $request->user()->id,
                'province_id'   => $request->user()->province_id,
                'phone_number'  => $request->user()->phone_number
            ]);

            // Validate the data
            $validator = Validator::make($data, $this->restaurantRules());

            if ($validator->fails()) {
                // Convert validation messages to an array
                $errors = $validator->errors()->toArray();
                info($errors);
                return $this->errorResponse('Validation failed', 422, $errors);
            }

            // Handle image upload if present
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('restaurants', 'public');
                $data['image'] = $imagePath;
            } else {
                $data['image'] = null;
            }

            $existingRestaurant = Restaurant::where('user_id', $request->user()->id)->first();
            $user = User::where('id', $request->user()->id)->first();

            if ($user->user_type === 'restaurant' && $existingRestaurant) {
                return $this->errorResponse('User already own a restaurant.', 400);
            }


            DB::beginTransaction();
            $restaurant = Restaurant::create($data);
            $user->update(['user_type' => 'restaurant']);
            // Commit the transaction
            DB::commit();

            return $this->successResponse($restaurant, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack(); // Rollback the transaction on validation error
            return $this->errorResponse(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction on general error
            return $this->errorResponse(['errors' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            info('Restaurant failed to get');
            return $this->errorResponse('Restaurant failed to get', 500);
        }

        $province = Province::where('id', $restaurant->province_id)->value('name');
        $owner = User::where('id', $restaurant->user_id)->value('username');
        $close_at =  Carbon::parse($restaurant->open_at)->format('H:i');
        $open_at =  Carbon::parse($restaurant->close_at)->format('H:i');


        $restaurant = [
            'id'            => $restaurant->id,
            'name'          => $restaurant->name,
            'owner'         => $owner,
            'province'      => $province,
            'address'       => $restaurant->address,
            'description'   => $restaurant->description,
            'thumbnail'     => $restaurant->image,
            'open_at'       => $open_at,
            'close_at'      => $close_at,
        ];
        return $this->successResponse($restaurant);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $user_id = $request->user()->id;

        $restaurant = Restaurant::where('user_id', $user_id)->first();

        if (!$restaurant) {
            info('Restaurant not found');
            return $this->errorResponse('Restaurant failed to update');
        }

        if (!$request->hasAny(['name', 'province_id', 'address', 'description', 'image', 'open_at', 'close_at'])) {
            return $this->errorResponse('Restaurant failed to update');
        }


        DB::table('restaurants')->where('id', $restaurant->id)->update([
            'name'         => $request->name          ?? $restaurant->name,
            'user_id'      => $restaurant->user_id,
            'province_id'  => $request->province_id   ?? $restaurant->province_id,
            'address'      => $request->address       ?? $restaurant->address,
            'description'  => $request->description   ?? $restaurant->description,
            'image'        => $request->image         ?? $restaurant->image,
            'open_at'      => $request->open_at       ?? $restaurant->open_at,
            'close_at'     => $request->close_at      ?? $restaurant->close_at,
        ]);

        return $this->successResponse('Restaurant updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $user_id = $request->user()->id;
        $restaurant = Restaurant::where('user_id', $user_id)->first();

        if (!$restaurant) {
            return $this->errorResponse('Restaurant not found', 404);
        }
 
        try 
        {

            DB::beginTransaction();

            User::where('id', $user_id)->update([
                'user_type' => 'customer'
            ]);

            $restaurant->delete();

            DB::commit();

            return $this->successResponse('Restaurant deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse( 'Failed to delete restaurant.' , 500);
        }
    }


    public function popular()
    {
        $restaurantOrders = DB::table('orders as b')
            ->leftJoin('users as u', 'u.id', '=', 'b.user_id')
            ->leftJoin('restaurants as r', 'r.id', '=', 'o.hotel_id')
            ->select(
                'r.id as restaurant_id',
                'r.name as restaurant_name',
                DB::raw('count(*) as popular_point')
            )
            ->groupBy(
                'r.id',
                'r.name'
            )
            ->get();

        return $this->successResponse($restaurantOrders);
    }
}
