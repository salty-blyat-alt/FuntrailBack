<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->id(); // Primary key for the hotels table
            $table->string('name'); // Hotel name
            $table->string('province'); // Province or location
            $table->text('address'); // Address of the hotel
            $table->text('description'); // Description of the hotel
            $table->integer('room_available'); // Number of rooms available
            $table->string('phone_number'); // Contact phone number
            $table->time('open_at'); // Opening time
            $table->time('close_at'); // Closing time

            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
