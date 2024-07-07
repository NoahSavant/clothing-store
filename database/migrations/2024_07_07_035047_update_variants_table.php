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
        Schema::table('variants', function (Blueprint $table) {
            $table->unsignedBigInteger('product_color_id')->nullable()->after('product_id');
            $table->unsignedBigInteger('product_size_id')->nullable()->after('product_color_id');

            $table->foreign('product_color_id')->references('id')->on('product_colors');
            $table->foreign('product_size_id')->references('id')->on('product_sizes');

            // $table->dropColumn('color');
            // $table->dropColumn('size');
            // $table->dropColumn('image_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('variants', function (Blueprint $table) {
            // $table->string('color')->nullable()->after('product_id');
            // $table->string('size')->nullable()->after('color');
            // $table->string('image_url')->nullable()->after('stock_limit');

            $table->dropForeign(['product_color_id']);
            $table->dropForeign(['product_size_id']);
            $table->dropColumn('product_color_id');
            $table->dropColumn('product_size_id');
        });
    }
};
