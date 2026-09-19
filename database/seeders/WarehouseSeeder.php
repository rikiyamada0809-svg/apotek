<?php

namespace Database\Seeders;

use App\Models\StorageLocation;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Gudang Farmasi Utama
        $mainWarehouse = Warehouse::firstOrCreate(
            ['name' => 'Gudang Farmasi Utama'],
            [
                'address' => 'Jl. Farmasi Sehat No. 10, Lantai 2 (Gudang Induk)',
                'type' => 'main',
                'is_active' => true,
            ]
        );

        $mainLocations = [
            ['code' => 'RAK-A1', 'name' => 'Rak Obat Bebas (OTC) & Sirup', 'type' => 'rak', 'temperature_range' => '15-25°C'],
            ['code' => 'RAK-A2', 'name' => 'Rak Obat Bebas Terbatas & Vitamin', 'type' => 'rak', 'temperature_range' => '15-25°C'],
            ['code' => 'RAK-B1', 'name' => 'Rak Obat Keras & Antibiotik', 'type' => 'rak', 'temperature_range' => '15-25°C'],
            ['code' => 'RAK-B2', 'name' => 'Rak Salep, Tetes Mata & Topikal', 'type' => 'rak', 'temperature_range' => '15-25°C'],
            ['code' => 'CHILLER-01', 'name' => 'Kulkas Pendingin (Insulin & Vaksin)', 'type' => 'kulkas', 'temperature_range' => '2-8°C'],
            ['code' => 'LEMARI-NARK', 'name' => 'Lemari Khusus Narkotika & Psikotropika', 'type' => 'lemari_narkotika', 'temperature_range' => '15-25°C'],
        ];

        foreach ($mainLocations as $loc) {
            StorageLocation::firstOrCreate(
                [
                    'warehouse_id' => $mainWarehouse->id,
                    'code' => $loc['code'],
                ],
                array_merge($loc, ['is_active' => true])
            );
        }

        // 2. Depo Pelayanan & Kasir Depan
        $frontDepo = Warehouse::firstOrCreate(
            ['name' => 'Depo Apotek / Kasir Depan'],
            [
                'address' => 'Area Kasir & Pelayanan Resep Lantai 1',
                'type' => 'branch',
                'is_active' => true,
            ]
        );

        $depoLocations = [
            ['code' => 'KASIR-01', 'name' => 'Etalase Kasir Fast-Moving', 'type' => 'rak', 'temperature_range' => '15-25°C'],
            ['code' => 'RACIK-01', 'name' => 'Meja & Rak Resep Racikan', 'type' => 'rak', 'temperature_range' => '15-25°C'],
        ];

        foreach ($depoLocations as $loc) {
            StorageLocation::firstOrCreate(
                [
                    'warehouse_id' => $frontDepo->id,
                    'code' => $loc['code'],
                ],
                array_merge($loc, ['is_active' => true])
            );
        }
    }
}
