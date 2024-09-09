<?php

namespace App\Http\Controllers\Hotel;

use App\Models\Hotel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller; 
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
        'provinces.name as province') 
        ->leftJoin('provinces', 'provinces.id', '=', 'hotels.province_id')
        ->paginate($perPage);
    
    $hotels = cleanPagination($hotels);
    
    return $this->successResponse($hotels);
}

    // work done
    public function store(Request $request)
    {
        $validatedData = Validator::make(array_merge(
            $request->all(),[
                'user_id'       => $request->user()->id,
                'province_id'   => $request->user()->province_id,
                'phone_number' => $request->user()->phone_number
            ]), $this->hotelRules()); 

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

    public function destroy(Hotel $hotel)
    {

        $hotel->delete();
        return $this->successResponse('Hotel deleted successfully');
    }
}
