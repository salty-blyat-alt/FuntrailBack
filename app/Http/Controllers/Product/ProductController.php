<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Restaurant;
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
    public function destroy(string $id) {}

    public function menu(string $id)
    {

        $products = Product::where('restaurant_id', $id)->get();

        // Check if products were found
        if ($products->isEmpty()) {
            return $this->errorResponse('No products found for this restaurant.', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse($products, Response::HTTP_NO_CONTENT);
    }

    public function addItem(Request $request)
    {
        // Retrieve the restaurant associated with the authenticated user
        $restaurant = Restaurant::where('user_id', $request->user()->id)->first();

        // Check if the restaurant exists
        if (!$restaurant) {
            return $this->successResponse('Restaurant not found', 404);
        }

        $restaurant_id = $restaurant->id;

        // Validate the incoming request
        $request->validate([
            'items' => 'required|array',
            'items.*.name'          => 'required|string|max:255',
            'items.*.is_instock'    => 'boolean',
            'items.*.price'         => 'required|numeric|min:0',
            'items.*.image'         => 'nullable|file|mimes:jpeg,png,jpg',
        ]);

        $createdItems = []; // Array to hold created items

        // Loop through each item in the items array
        foreach ($request->items as $itemData) {
            // Upload the image and get the path
            $imagePath = uploadDocument($itemData['image'], 'restaurants/r-' . $restaurant_id);

            // Create a new product
            $item = Product::create([
                'name' => $itemData['name'],
                'is_instock' => $itemData['is_instock'] ?? true,
                'price' => $itemData['price'],
                'restaurant_id' => $restaurant_id,
                'image' => $imagePath,
            ]);

            $createdItems[] = $item;
        }

        // Return a success response with the created items
        return $this->successResponse($createdItems);
    }

    public function toggleItemStock(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'item_id' => 'required|integer|exists:products,id',
        ]);

        // Retrieve the product by its ID
        $item = Product::find($request->item_id);

        // Check if the product exists
        if (!$item) {
            return $this->errorResponse('Product not found', 404);
        }

        // Toggle the stock status
        $item->is_instock = !$item->is_instock; // Toggle the current stock status
        $item->save(); // Save the changes

        // Return a success response with the updated item
        return $this->successResponse($item);
    }
}
