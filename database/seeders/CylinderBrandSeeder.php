<?php

namespace Database\Seeders;

use App\Models\CylinderBrand;
use App\Models\CylinderType;
use Illuminate\Database\Seeder;

class CylinderBrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // List of brands and their corresponding cylinder types
        $brands = [
            'HP Gas' => [
                ['weight' => 12, 'description' => 'Standard domestic LPG cylinder size for small families or kitchens.'],
                ['weight' => 14.2, 'description' => 'Another popular size for domestic use (usually the most common size).'],
                ['weight' => 5, 'description' => 'Smaller cylinder for single persons or small households.'],
                ['weight' => 19, 'description' => 'Available for commercial use or larger families.'],
            ],
            'Indane Gas' => [
                ['weight' => 14.2, 'description' => 'Common size for residential use (most popular).'],
                ['weight' => 5, 'description' => 'For small families or single persons.'],
                ['weight' => 19, 'description' => 'Used for larger families or commercial purposes.'],
                ['weight' => 47.5, 'description' => 'For commercial purposes, typically used in restaurants and other businesses.'],
            ],
            'Bharat Gas' => [
                ['weight' => 14.2, 'description' => 'The standard residential cylinder size.'],
                ['weight' => 5, 'description' => 'For smaller households or single persons.'],
                ['weight' => 19, 'description' => 'Larger cylinder for larger households or commercial use.'],
                ['weight' => 47.5, 'description' => 'For commercial use (restaurants, etc.).'],
            ],
            'Reliance Gas' => [
                ['weight' => 14.2, 'description' => 'Common for residential use.'],
                ['weight' => 5, 'description' => 'For small homes, single persons, or small kitchens.'],
                ['weight' => 19, 'description' => 'Used for larger families or commercial establishments.'],
                ['weight' => 47.5, 'description' => 'Large cylinders for commercial use.'],
            ],
        ];

        // Loop through each brand and insert into CylinderBrand and CylinderType
        foreach ($brands as $brandName => $types) {
            $brand = CylinderBrand::firstOrCreate(['name' => $brandName]);

            // Loop through the cylinder types for each brand and insert them
            foreach ($types as $type) {
                CylinderType::firstOrCreate([
                    'brand_id' => $brand->id,
                    'weight' => $type['weight'],
                    'description' => $type['description'],
                ]);
            }
        }
    }
}
