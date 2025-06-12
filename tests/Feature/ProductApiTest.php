<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;
    
    protected $adminUser;
    protected $regularUser;
    
    public function setUp(): void
    {
        parent::setUp();
        
        // Create users for testing
        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
        
        $this->regularUser = User::factory()->create([
            'role' => 'user',
        ]);
    }
    
    /** @test */
    public function regular_users_can_view_products()
    {
        // Create some products
        Product::factory()->count(5)->create();
        
        // Test that a regular user can view products
        $response = $this->actingAs($this->regularUser)
                         ->getJson('/api/products');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'meta',
                 ]);
    }
    
    /** @test */
    public function regular_users_can_view_a_single_product()
    {
        // Create a product
        $product = Product::factory()->create();
        
        // Test that a regular user can view a single product
        $response = $this->actingAs($this->regularUser)
                         ->getJson('/api/products/' . $product->id);
        
        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'id' => $product->id,
                     'name' => $product->name,
                 ]);
    }
    
    /** @test */
    public function regular_users_cannot_create_products()
    {
        // Test data
        $productData = [
            'name' => 'Test Battery',
            'description' => 'A test battery',
            'price' => 1000,
            'quantity' => 10,
            'type' => 'battery',
            'specifications' => [
                'capacity' => 100,
                'voltage' => 12,
                'chemistry' => 'Lithium-Ion',
            ],
        ];
        
        // Test that a regular user cannot create a product
        $response = $this->actingAs($this->regularUser)
                         ->postJson('/api/products', $productData);
        
        $response->assertStatus(403); // Forbidden
    }
    
    /** @test */
    public function admin_users_can_create_products()
    {
        // Test data
        $productData = [
            'name' => 'Test Battery',
            'description' => 'A test battery',
            'price' => 1000,
            'quantity' => 10,
            'type' => 'battery',
            'specifications' => [
                'capacity' => 100,
                'voltage' => 12,
                'chemistry' => 'Lithium-Ion',
            ],
        ];
        
        // Test that an admin user can create a product
        $response = $this->actingAs($this->adminUser)
                         ->postJson('/api/products', $productData);
        
        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'name' => 'Test Battery',
                     'price' => 1000,
                     'type' => 'battery',
                 ]);
                 
        // Verify the product was created in the database
        $this->assertDatabaseHas('products', [
            'name' => 'Test Battery',
            'price' => 1000,
            'type' => 'battery',
        ]);
    }
    
    /** @test */
    public function admin_users_can_update_products()
    {
        // Create a product
        $product = Product::factory()->create([
            'name' => 'Original Name',
            'price' => 500,
        ]);
        
        // Test data
        $updateData = [
            'name' => 'Updated Name',
            'price' => 1000,
        ];
        
        // Test that an admin user can update a product
        $response = $this->actingAs($this->adminUser)
                         ->postJson('/api/products/' . $product->id, $updateData);
        
        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'name' => 'Updated Name',
                     'price' => 1000,
                 ]);
                 
        // Verify the product was updated in the database
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'price' => 1000,
        ]);
    }
    
    /** @test */
    public function admin_users_can_delete_products()
    {
        // Create a product
        $product = Product::factory()->create();
        
        // Test that an admin user can delete a product
        $response = $this->actingAs($this->adminUser)
                         ->deleteJson('/api/products/' . $product->id);
        
        $response->assertStatus(200);
                 
        // Verify the product was deleted from the database
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }
    
    /** @test */
    public function admin_users_can_toggle_product_status()
    {
        // Create a product with active status
        $product = Product::factory()->create([
            'status' => 'active',
        ]);
        
        // Test that an admin user can toggle the product status
        $response = $this->actingAs($this->adminUser)
                         ->getJson('/api/products/toggle-status/' . $product->id);
        
        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'status' => 'inactive',
                 ]);
                 
        // Verify the product status was updated in the database
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'inactive',
        ]);
    }
}