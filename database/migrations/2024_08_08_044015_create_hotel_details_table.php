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
        Schema::create('hotel_details', function (Blueprint $table) {
            $table->foreignId('room_type_id')->constrained()->onDelete('cascade'); // Foreign key to room_types table
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade'); // Foreign key to room_types table
            $table->boolean('is_available'); // Availability status (true/false)
            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_details');
    }
};
