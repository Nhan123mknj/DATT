<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CategoriesDevice>
 */
class CategoriesDeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $names = ['Máy chiếu', 'Laptop', 'Loa', 'Micro', 'Màn hình TV'];
        $name = $this->faker->unique()->randomElement($names);

        return [
            'name' => $name,
            'code' => strtoupper(substr(Str::ascii($name), 0, 3)) . $this->faker->unique()->numberBetween(100, 999),
            'description' => $this->faker->sentence(),
            'is_active' => $this->faker->boolean(90),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
