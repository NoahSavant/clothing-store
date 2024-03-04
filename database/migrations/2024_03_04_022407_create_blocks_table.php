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
        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('block_id')->nullable()->default(null);
            $table->unsignedBigInteger('page_id')->nullable();
            $table->integer('index')->nullable();
            $table->boolean('hide')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('block_id')->references('id')->on('blocks');
            $table->foreign('page_id')->references('id')->on('pages');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocks');
    }
};
