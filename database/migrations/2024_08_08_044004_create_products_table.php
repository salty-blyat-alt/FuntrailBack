<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // Primary key for the products table
            $table->string('name'); // Name of the product
            $table->integer('quantity'); // Quantity of the product in stock
            $table->decimal('price', 8, 2); // Price of the product (e.g., 999999.99)
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade'); // Foreign key to restaurants table
            $table->string('img')->nullable(); // Image URL or path (nullable if not required)
            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
