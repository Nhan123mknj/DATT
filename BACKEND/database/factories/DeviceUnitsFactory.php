<?php

namespace Database\Factories;

use App\Models\Devices;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeviceUnits>
 */
class DeviceUnitsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'device_id' => Devices::inRandomOrder()->first()->id ?? Devices::factory(),
            'serial_number' => strtoupper($this->faker->bothify('SN-###-???')),
            'status' => $this->faker->randomElement(['available', 'in_use', 'maintenance', 'broken']),
            'purchase_date' => $this->faker->dateTimeBetween('-3 years', 'now'),
            'warranty_end' => $this->faker->dateTimeBetween('now', '+3 years'),
            'notes' => $this->faker->sentence(),
        ];
    }
}
