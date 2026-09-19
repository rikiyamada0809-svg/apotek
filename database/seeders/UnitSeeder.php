<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Seed default measurement units for pharmacy.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'Box', 'abbreviation' => 'Box'],
            ['name' => 'Strip', 'abbreviation' => 'Str'],
            ['name' => 'Tablet', 'abbreviation' => 'Tab'],
            ['name' => 'Kapsul', 'abbreviation' => 'Kap'],
            ['name' => 'Botol', 'abbreviation' => 'Btl'],
            ['name' => 'Tube', 'abbreviation' => 'Tb'],
            ['name' => 'Sachet', 'abbreviation' => 'Sac'],
            ['name' => 'Ampul', 'abbreviation' => 'Amp'],
            ['name' => 'Vial', 'abbreviation' => 'Vial'],
            ['name' => 'Pcs', 'abbreviation' => 'Pcs'],
            ['name' => 'Lusin', 'abbreviation' => 'Lsn'],
            ['name' => 'Pack', 'abbreviation' => 'Pack'],
            ['name' => 'Kaleng', 'abbreviation' => 'Klg'],
            ['name' => 'Rol', 'abbreviation' => 'Rol'],
            ['name' => 'Lembar', 'abbreviation' => 'Lbr'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['name' => $unit['name']], $unit);
        }
    }
}
