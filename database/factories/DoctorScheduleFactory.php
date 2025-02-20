<?php

namespace Database\Factories;

use App\Enums\DoctorRecurring;
use App\Enums\DoctorScheduleStatus;
use App\Enums\Weekday;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DoctorSchedule>
 */
class DoctorScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'doctor_id' => Doctor::factory(),
            'weekday' => $this->faker->randomElement(Weekday::toArray()),
            'time' => $this->faker->time(),
            'recurring' => $this->faker->randomElement(DoctorRecurring::toArray()),
            'seats' => $this->faker->numberBetween(1, 50),
            'status' => $this->faker->randomElement(DoctorScheduleStatus::toArray()),
        ];
    }
}
