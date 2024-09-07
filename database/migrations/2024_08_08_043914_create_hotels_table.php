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
            $table->id();
            $table->string('name');
            $table->integer('user_id')->constrained()->onDelete('cascade');  
            $table->foreignId('province_id')->constrained()->onDelete('cascade'); 
            $table->text('address');  
            $table->text('description')->nullable(); 
            $table->string('thumbnail');
            $table->json('images');
            $table->time('open_at');  
            $table->time('close_at'); 

            $table->timestamps();  
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
