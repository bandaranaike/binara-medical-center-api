<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Doctor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'hospital_id' => rand(1, 2),
            'specialty_id' => rand(1, 50),
            'telephone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'doctor_type' => [Doctor::DOCTOR_TYPE_DENTAL, Doctor::DOCTOR_TYPE_OPD, Doctor::DOCTOR_TYPE_SPECIALIST][rand(0, 2)],
            'user_id' => User::where('role_id', 4)->inRandomOrder()->first()?->id ?? User::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
