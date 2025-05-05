<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'code' => 'SU01',
                'name' => 'PT Sumber Makmur',
                'phone' => '081234567890',
                'email' => 'makmur@example.com',
                'address' => 'Jl. Merdeka No. 1, Jakarta',
                'is_active' => true,
            ],
            [
                'code' => 'SU02',
                'name' => 'CV Jaya Abadi',
                'phone' => '082345678901',
                'email' => 'jayaabadi@example.com',
                'address' => 'Jl. Sudirman No. 45, Bandung',
                'is_active' => true,
            ],
            [
                'code' => 'SU03',
                'name' => 'UD Sentosa',
                'phone' => '083456789012',
                'email' => 'sentosa@example.com',
                'address' => 'Jl. Pahlawan No. 10, Surabaya',
                'is_active' => false,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
