<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Piece', 'symbol' => 'Pcs', 'description' => 'Piece(s)'],
            ['name' => 'Kilogram', 'symbol' => 'Kg', 'description' => 'Kilogram'],
            ['name' => 'Litre', 'symbol' => 'L', 'description' => 'Litre'],
            ['name' => 'Metre', 'symbol' => 'M', 'description' => 'Metre'],
            ['name' => 'Box', 'symbol' => 'Box', 'description' => 'Box'],
        ];
        foreach ($units as $u) {
            Unit::firstOrCreate(['symbol' => $u['symbol']], $u);
        }
    }
}
