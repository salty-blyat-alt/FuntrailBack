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
        $hotels = Hotel::paginate($perPage);
        $hotels = cleanPagination($hotels);
        return $this->successResponse($hotels);
    }

    // work done
    public function store(Request $request)
    {
        
        $validatedData = Validator::make($request->all(), $this->hotelRules()); 

        if ($validatedData->fails()) {
            info($validatedData->messages()); 
            return $this->errorResponse("Hotel failed to create", 500);
        }

        $thumbnailPath = uploadDocument($request->file('thumbnail'), 'hotels/thumbnails');

        // Upload multiple images if provided
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
            'user_id'      => $request->user_id,
            'province_id'  => $request->province_id,
            'address'      => $request->address,
            'description'  => $request->description ?? null,
            'phone_number' => $request->phone_number,
            'thumbnail'    => $thumbnailPath ?? null,
            'images'       => $imagesJson ?? null,
            'open_at'      => $request->open_at,
            'close_at'     => $request->close_at,
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
            info('Error show: Approver not found');
            return $this->errorResponse('Approver failed to get', 500);
        }

        return $this->successResponse($hotel);
    }



    public function update(Request $request, string $id)
    {
        $hotel = Hotel::find($id);
        $validatedData = Validator::make($request->all(), $this->hotelRules());

        if (!$hotel) {
            info('Approver not found');
            return $this->errorResponse('Approver failed to update', 500);
        }

        DB::table('hotels')->where('id', $id)->update([
            'name'         => $request->name,
            'user_id'      => $request->user_id,
            'province_id'  => $request->province_id,
            'address'      => $request->address,
            'description'  => $request->description ?? null,
            'phone_number' => $request->phone_number,
            'thumbnail'    => $request->thumbnail,
            'images'       => $request->images,
            'open_at'      => $request->open_at,
            'close_at'     => $request->close_at,
        ]);
        return response()->json($hotel);
    }

    public function destroy(Hotel $hotel)
    {
        $hotel->delete();
        return $this->successResponse('Hotel deleted successfully');
    }
 
}
