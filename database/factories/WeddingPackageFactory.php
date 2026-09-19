<?php

namespace Database\Factories;

use App\Models\WeddingPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WeddingPackage>
 */
class WeddingPackageFactory extends Factory
{
    protected $model = WeddingPackage::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
            'description' => fake()->sentence(),
            'sections' => [
                ['title' => 'Term of Payment', 'items' => ['Booking 30%', 'Pelunasan maksimal H-7']],
            ],
            'price' => fake()->numberBetween(10000000, 100000000),
            'is_active' => false,
            'sort_order' => 0,
        ];
    }
}
