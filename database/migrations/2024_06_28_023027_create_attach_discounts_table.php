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
        Schema::create('attach_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('discount_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('discount_id')->references('id')->on('discounts');
            $table->foreign('order_id')->references('id')->on('orders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attach_discounts');
    }
};
