<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Drug;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Brand>
 */
class BrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company() . ' Pharma',
            'drug_id' => Drug::inRandomOrder()->first()->id ?? Drug::factory(),
        ];
    }
}
