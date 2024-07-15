<?php

namespace Database\Seeders;

use App\Models\ProductColor;
use App\Models\ProductSize;
use App\Models\Variant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateVariantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // $variants = Variant::get();

        // foreach ($variants as $variant) {
        //     $color = ProductColor::create([
        //         'product_id' => $variant->product_id,
        //         'color' => $variant->color,
        //         'image_url' => $variant->image_url
        //     ]);

        //     $size = ProductSize::create([
        //         'product_id' => $variant->product_id,
        //         'size' => $variant->size
        //     ]);

        //     Variant::where('id', $variant->id)
        //         ->update([
        //             'product_color_id' => $color->id,
        //             'product_size_id' => $size->id,
        //         ]);
        // }
    }
}
