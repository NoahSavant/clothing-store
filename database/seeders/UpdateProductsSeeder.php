<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductSize;
use App\Models\Variant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $products = Product::get();

        foreach ($products as $product) {
            $productSize = ProductSize::where('product_id', $product->id)->first();

            if ($productSize) {
                ProductSize::where('product_id', $product->id)
                    ->where('id', '!=', $productSize->id)
                    ->delete();

                Variant::where('product_id', $product->id)
                    ->update([
                        'product_size_id' => $productSize->id
                    ]);
            }
        }
    }
}
