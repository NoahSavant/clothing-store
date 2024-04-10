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
        Schema::create('import_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->unsignedBigInteger('import_id')->nullable();
            $table->unsignedBigInteger('amount')->nullable();
            $table->unsignedBigInteger('unit_price')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('variant_id')->references('id')->on('variants');
            $table->foreign('import_id')->references('id')->on('imports');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_variants');
    }
};
