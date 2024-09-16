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
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->constrained()->onDelete('cascade');
            $table->string('payment_type');
            $table->decimal('total_payment', 8,2);
            $table->integer('commission_rate')->default(0);
            $table->decimal('total_commission',8,2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commisions');
    }
};
