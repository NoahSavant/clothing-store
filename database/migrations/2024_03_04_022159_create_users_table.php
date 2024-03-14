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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->integer('role')->nullable();
            $table->string('image_url')->nullable();
            $table->string('phonenumber')->nullable();
            $table->string('verify_code')->nullable();
            $table->timestamp('overtimed_at')->nullable();
            $table->integer('status')->nullable();
            $table->integer('gender')->nullable();
            $table->string('remember_token', 400)->nullable();
            $table->timestamp('date_of_birth')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
