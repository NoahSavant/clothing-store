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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedBigInteger('total_price')->nullable();
            $table->integer('transportation_method')->nullable();
            $table->unsignedBigInteger('product_price')->nullable();
            $table->unsignedBigInteger('transport_price')->nullable();
            $table->unsignedBigInteger('paid')->nullable();
            $table->unsignedBigInteger('product_discount')->nullable();
            $table->unsignedBigInteger('transport_discount')->nullable();
            $table->integer('payment_method')->nullable();
            $table->integer('status')->nullable();
            $table->string('phonenumber')->nullable();
            $table->string('address')->nullable();
            $table->string('note')->nullable();
            $table->string('address_link')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('staff_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
