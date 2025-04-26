<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['code' => 'ml', 'name' => 'Milliliter'],
            ['code' => 'l', 'name' => 'Liter'],
            ['code' => 'g', 'name' => 'Gram'],
            ['code' => 'kg', 'name' => 'Kilogram'],
            ['code' => 'pcs', 'name' => 'Pieces'],
            ['code' => 'box', 'name' => 'Box'],
            ['code' => 'pack', 'name' => 'Pack'],
            ['code' => 'set', 'name' => 'Set'],
            ['code' => 'btl', 'name' => 'Bottle'],
            ['code' => 'tube', 'name' => 'Tube'],
            ['code' => 'sct', 'name' => 'Sachet'],
            ['code' => 'ctn', 'name' => 'Carton'],
            ['code' => 'can', 'name' => 'Can'],
            ['code' => 'jar', 'name' => 'Jar'],
            ['code' => 'unit', 'name' => 'Unit'],
        ];

        foreach ($units as $unit) {
            DB::table('units')->updateOrInsert(
                ['code' => $unit['code']],
                [
                    'name' => $unit['name'],
                    'slug' => Str::slug($unit['name']),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
