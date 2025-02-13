<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Drug;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DrugSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get(base_path('database/data/Drugs.json')); // Path to your JSON file
        $data = json_decode($json);

        foreach ($data as $item) {
            $categoryName = $item->category;

            // Find or create the category
            $category = Category::firstOrCreate(['name' => $categoryName]);

            foreach ($item->drugs as $drugName) {
                // Check if the drug already exists to prevent duplicates.
                $existingDrug = Drug::where('name', $drugName)->first();

                if (!$existingDrug) {
                    Drug::create([
                        'name' => $drugName,
                        'minimum_quantity' => 1, // Set a default minimum quantity
                        'category_id' => $category->id,
                    ]);
                }
            }
        }
    }
}
