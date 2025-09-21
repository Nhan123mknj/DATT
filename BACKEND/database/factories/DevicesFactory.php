<?php

namespace Database\Factories;

use App\Models\CategoriesDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Devices>
 */
class DevicesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'model' => $this->faker->bothify('Model-###??'),
            'category_id' => CategoriesDevice::inRandomOrder()->first()->id ?? CategoriesDevice::factory(),
            'manufacturer' => $this->faker->randomElement(['Dell', 'HP', 'Epson', 'Sony', 'JBL']),
            'specifications' => json_encode([
                'CPU' => $this->faker->randomElement(['Intel i5', 'Intel i7', 'Ryzen 5']),
                'RAM' => $this->faker->randomElement(['8GB', '16GB', '32GB']),
                'Storage' => $this->faker->randomElement(['256GB SSD', '512GB SSD', '1TB SSD']),
                'Display' => $this->faker->randomElement(['13.3 inch FHD', '15.6 inch FHD', '27 inch 4K']),
            ]),
            'is_active' => $this->faker->boolean(95),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
