<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['battery', 'solar_panel', 'inverter'];
        $type = $this->faker->randomElement($types);
        
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 100, 10000),
            'quantity' => $this->faker->numberBetween(0, 100),
            'type' => $type,
            'specifications' => $this->getSpecificationsForType($type),
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }
    
    /**
     * Configure the model factory for battery products.
     */
    public function battery(): self
    {
        return $this->state(function () {
            return [
                'type' => 'battery',
                'specifications' => $this->getSpecificationsForType('battery'),
            ];
        });
    }
    
    /**
     * Configure the model factory for solar panel products.
     */
    public function solarPanel(): self
    {
        return $this->state(function () {
            return [
                'type' => 'solar_panel',
                'specifications' => $this->getSpecificationsForType('solar_panel'),
            ];
        });
    }
    
    /**
     * Configure the model factory for inverter products.
     */
    public function inverter(): self
    {
        return $this->state(function () {
            return [
                'type' => 'inverter',
                'specifications' => $this->getSpecificationsForType('inverter'),
            ];
        });
    }
    
    /**
     * إضافة صور للمنتج
     */
    public function withImages(int $count = 3): self
    {
        return $this->afterCreating(function (Product $product) use ($count) {
            // إنشاء صورة رئيسية
            \App\Models\ProductImage::factory()
                ->primary()
                ->create(['product_id' => $product->id]);
            
            // إنشاء الصور الإضافية
            \App\Models\ProductImage::factory()
                ->count($count - 1)
                ->create(['product_id' => $product->id]);
        });
    }
    
    /**
     * Generate specifications based on product type.
     */
    private function getSpecificationsForType(string $type): array
    {
        switch ($type) {
            case 'battery':
                return [
                    'capacity' => $this->faker->randomFloat(2, 50, 500),
                    'voltage' => $this->faker->randomElement([12, 24, 48]),
                    'chemistry' => $this->faker->randomElement(['Lithium-Ion', 'Lead-Acid', 'LiFePO4']),
                    'cycle_life' => $this->faker->numberBetween(500, 5000),
                    'dimensions' => $this->faker->numberBetween(10, 50) . 'x' . 
                                   $this->faker->numberBetween(10, 50) . 'x' . 
                                   $this->faker->numberBetween(10, 50) . ' cm',
                    'weight' => $this->faker->randomFloat(1, 5, 100),
                    'brand' => $this->faker->company(),
                ];
                
            case 'solar_panel':
                return [
                    'power' => $this->faker->randomElement([100, 200, 250, 300, 350, 400]),
                    'voltage' => $this->faker->randomFloat(1, 20, 50),
                    'current' => $this->faker->randomFloat(2, 5, 15),
                    'dimensions' => $this->faker->numberBetween(100, 200) . 'x' . 
                                   $this->faker->numberBetween(50, 100) . 'x' . 
                                   $this->faker->numberBetween(3, 5) . ' cm',
                    'weight' => $this->faker->randomFloat(1, 10, 30),
                    'cell_type' => $this->faker->randomElement(['Monocrystalline', 'Polycrystalline', 'Thin-Film']),
                    'efficiency' => $this->faker->randomFloat(1, 15, 22),
                    'brand' => $this->faker->company(),
                ];
                
            case 'inverter':
                return [
                    'power' => $this->faker->randomElement([1000, 2000, 3000, 5000]),
                    'input_voltage' => $this->faker->randomElement([12, 24, 48]),
                    'output_voltage' => $this->faker->randomElement([110, 220, 230, 240]),
                    'efficiency' => $this->faker->randomFloat(1, 90, 98),
                    'dimensions' => $this->faker->numberBetween(30, 60) . 'x' . 
                                   $this->faker->numberBetween(20, 40) . 'x' . 
                                   $this->faker->numberBetween(10, 20) . ' cm',
                    'weight' => $this->faker->randomFloat(1, 5, 30),
                    'type' => $this->faker->randomElement(['Pure Sine Wave', 'Modified Sine Wave', 'Grid-Tie']),
                    'brand' => $this->faker->company(),
                ];
                
            default:
                return [];
        }
    }
} 