<?php

namespace Database\Seeders;

use App\Events\MasterProductNew;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'code' => 'NRM',
                'name' => 'Nurma',
                'variant_name' => 'Black Opium',
                'default_unit_id' => 9,
                'purchase_price' => 45000,
                'selling_price' => 50000,
                'description' => 'Parfume Nurma Best Seller',
                'is_active' => true,
            ],
            [
                'code' => 'GE',
                'name' => 'Gee',
                'variant_name' => 'YSL',
                'default_unit_id' => 9,
                'purchase_price' => 55000,
                'selling_price' => 60000,
                'description' => 'Parfume Gee Best Seller',
                'is_active' => true,
            ],
            [
                'code' => 'SDR',
                'name' => 'Sandra',
                'variant_name' => 'YSL Axe',
                'default_unit_id' => 9,
                'purchase_price' => 50000,
                'selling_price' => 55000,
                'description' => 'Sandra Best Seller',
                'is_active' => false,
            ],
        ];

        foreach ($products as $product) {
            $dataProduk = Product::create($product);

            Event::dispatch(new MasterProductNew($dataProduk));
        }
    }
}
