<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 10 batteries with images
        Product::factory()
            ->battery()
            ->count(10)
            ->withImages(3)
            ->create();
        
        // Create 10 solar panels with images
        Product::factory()
            ->solarPanel()
            ->count(10)
            ->withImages(4)
            ->create();
        
        // Create 10 inverters with images
        Product::factory()
            ->inverter()
            ->count(10)
            ->withImages(2)
            ->create();
    }
} 