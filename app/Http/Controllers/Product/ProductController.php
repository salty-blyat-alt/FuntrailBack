<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Retrieve all products
        $products = Product::all();

        // Return success response with products
        return $this->successResponse($products);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric',
            'restaurant_id' => 'required|integer|exists:restaurants,id',
            'image' => 'nullable|string',
        ]);

        $product = Product::create($request->all());

        return $this->successResponse($product, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with('restaurant', 'orderDetails')->findOrFail($id);
        return $this->successResponse($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric',
            'restaurant_id' => 'required|integer|exists:restaurants,id',
            'image' => 'nullable|string',
        ]);

        $product = Product::findOrFail($id);
        $product->update($request->all());

        return $this->successResponse($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $products = Product::where('restaurant', $id);
        $product->delete();

        return $this->successResponse(null, Response::HTTP_NO_CONTENT);
    }

    public function menu(string $id)
    {

        $products = Product::where('restaurant_id', $id)->get();

        // Check if products were found
        if ($products->isEmpty()) {
            return $this->errorResponse('No products found for this restaurant.', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse($products, Response::HTTP_NO_CONTENT);
    }
}
