<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductImage>
 */
class ProductImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductImage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // إنشاء مسار وهمي للصورة
        $imagePath = 'products/dummy_' . $this->faker->uuid . '.jpg';
        
        return [
            'product_id' => Product::factory(),
            'image_path' => $imagePath,
            'is_primary' => false,
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }
    
    /**
     * تحديد الصورة كصورة رئيسية
     */
    public function primary(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_primary' => true,
                'sort_order' => 0,
            ];
        });
    }
} 