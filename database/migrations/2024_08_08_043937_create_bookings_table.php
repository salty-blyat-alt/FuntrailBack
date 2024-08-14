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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id(); // Primary key for the bookings table
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade'); // Foreign key to hotels table
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Foreign key to users table
            $table->date('check_in'); // Check-in date
            $table->date('check_out'); // Check-out date
            $table->decimal('sum_total', 10, 2); // Total amount for the booking (e.g., 99999999.99)

            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
