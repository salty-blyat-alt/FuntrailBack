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
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id(); // Primary key for the hotels table
            $table->integer('user_id'); // Hotel name
            $table->string('name'); // Hotel name
            $table->foreignId('province_id')->constrained()->onDelete('cascade'); // Province or location with foreign key            $table->text('address'); // Address of the hotel
            $table->string('address'); // Province or location with foreign key            $table->text('address'); // Address of the hotel
            $table->text('description')->nullable(); // Description of the hotel
            $table->string('phone_number')->nullable(); // Contact phone number
            $table->string('image'); // profile
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
        Schema::dropIfExists('restaurants');
    }
};
