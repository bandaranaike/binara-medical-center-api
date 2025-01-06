<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'age' => $this->faker->numberBetween(1, 100),
            'address' => $this->faker->address(),
            'telephone' => $this->faker->phoneNumber(),
            'birthday' => $this->faker->date('Y-m-d'),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'email' => $this->faker->unique()->safeEmail(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
