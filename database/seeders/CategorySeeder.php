<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed default drug categories.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Obat',
                'description' => 'Semua jenis obat-obatan',
                'children' => [
                    ['name' => 'Antibiotik', 'description' => 'Obat anti infeksi bakteri'],
                    ['name' => 'Analgesik & Antipiretik', 'description' => 'Obat pereda nyeri dan penurun demam'],
                    ['name' => 'Antihipertensi', 'description' => 'Obat tekanan darah tinggi'],
                    ['name' => 'Antidiabetes', 'description' => 'Obat diabetes mellitus'],
                    ['name' => 'Antihistamin', 'description' => 'Obat anti alergi'],
                    ['name' => 'Obat Batuk & Flu', 'description' => 'Obat batuk, pilek, dan influenza'],
                    ['name' => 'Obat Maag & Pencernaan', 'description' => 'Obat saluran cerna'],
                    ['name' => 'Kortikosteroid', 'description' => 'Obat anti radang golongan steroid'],
                    ['name' => 'Obat Mata', 'description' => 'Tetes mata dan salep mata'],
                    ['name' => 'Obat THT', 'description' => 'Obat telinga, hidung, tenggorokan'],
                    ['name' => 'Obat Kulit', 'description' => 'Krim, salep, dan obat kulit'],
                ],
            ],
            [
                'name' => 'Vitamin & Suplemen',
                'description' => 'Vitamin, mineral, dan suplemen kesehatan',
                'children' => [
                    ['name' => 'Vitamin', 'description' => 'Vitamin A, B, C, D, E, K'],
                    ['name' => 'Mineral', 'description' => 'Kalsium, zat besi, zinc, dll'],
                    ['name' => 'Suplemen Herbal', 'description' => 'Suplemen berbahan herbal'],
                    ['name' => 'Suplemen Anak', 'description' => 'Vitamin dan suplemen untuk anak'],
                ],
            ],
            [
                'name' => 'Alat Kesehatan',
                'description' => 'Alat-alat kesehatan dan medis',
                'children' => [
                    ['name' => 'Alat Diagnostik', 'description' => 'Termometer, tensimeter, dll'],
                    ['name' => 'Alat Suntik & Infus', 'description' => 'Spuit, infus set, dll'],
                    ['name' => 'Perban & Plester', 'description' => 'Perban, kasa, plester'],
                    ['name' => 'Masker & APD', 'description' => 'Masker, sarung tangan, dll'],
                ],
            ],
            [
                'name' => 'Perawatan Tubuh',
                'description' => 'Produk perawatan tubuh dan kebersihan',
                'children' => [
                    ['name' => 'Perawatan Kulit', 'description' => 'Sabun, lotion, sunscreen'],
                    ['name' => 'Perawatan Mulut', 'description' => 'Pasta gigi, obat kumur'],
                    ['name' => 'Perawatan Rambut', 'description' => 'Shampo, kondisioner'],
                ],
            ],
            [
                'name' => 'Susu & Nutrisi',
                'description' => 'Susu formula, susu ibu hamil, nutrisi khusus',
                'children' => [
                    ['name' => 'Susu Bayi & Anak', 'description' => 'Susu formula untuk bayi dan anak'],
                    ['name' => 'Susu Ibu Hamil', 'description' => 'Susu untuk ibu hamil dan menyusui'],
                    ['name' => 'Susu Dewasa', 'description' => 'Susu rendah lemak, tinggi kalsium, dll'],
                ],
            ],
        ];

        foreach ($categories as $parentData) {
            $children = $parentData['children'] ?? [];
            unset($parentData['children']);

            $parent = Category::create($parentData);

            foreach ($children as $childData) {
                $parent->children()->create($childData);
            }
        }
    }
}
