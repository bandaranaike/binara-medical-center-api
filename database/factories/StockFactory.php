<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Stock;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stock>
 */
class StockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrice = $this->faker->randomFloat(2, 5, 100);
        $initialQuantity = $this->faker->numberBetween(10, 1000);
        $cost = $unitPrice * $initialQuantity;
        $discountedCost = $cost * 0.9; // Always company has 10%

        return [
            'brand_id' => Brand::factory(),
            'supplier_id' => Supplier::factory(),
            'unit_price' => $unitPrice,
            'batch_number' => $this->faker->unique()->regexify('[A-Z0-9]{8}'),
            'initial_quantity' => $initialQuantity,
            'quantity' => $initialQuantity, // Start with full quantity
            'expire_date' => $this->faker->dateTimeBetween('+1 year', '+3 years')->format('Y-m-d'),
            'cost' => $discountedCost,
        ];
    }
}
