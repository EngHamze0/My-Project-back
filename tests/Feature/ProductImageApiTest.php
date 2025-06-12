<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageApiTest extends TestCase
{
    use RefreshDatabase;
    
    protected $adminUser;
    protected $regularUser;
    protected $product;
    
    public function setUp(): void
    {
        parent::setUp();
        
        // إنشاء مستخدمين للاختبار
        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
        
        $this->regularUser = User::factory()->create([
            'role' => 'user',
        ]);
        
        // إنشاء منتج للاختبار
        $this->product = Product::factory()->create();
        
        // إنشاء تخزين وهمي للاختبار
        Storage::fake('public');
    }
    
    /** @test */
    public function regular_users_can_view_product_images()
    {
        // إنشاء بعض الصور للمنتج
        ProductImage::factory()->count(3)->create([
            'product_id' => $this->product->id,
        ]);
        
        // اختبار أن المستخدم العادي يمكنه رؤية صور المنتج
        $response = $this->actingAs($this->regularUser)
                         ->getJson("/api/products/{$this->product->id}/images");
        
        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }
    
    /** @test */
    public function admin_users_can_upload_product_image()
    {
        // إنشاء ملف وهمي للتحميل
        $file = UploadedFile::fake()->image('product.jpg');
        
        // اختبار أن المستخدم المشرف يمكنه تحميل صورة للمنتج
        $response = $this->actingAs($this->adminUser)
                         ->postJson("/api/products/{$this->product->id}/images", [
                             'image' => $file,
                             'is_primary' => true,
                         ]);
        
        $response->assertStatus(200);
        
        // التحقق من أن الصورة تم تخزينها
        $imagePath = $response->json('image_path');
        Storage::disk('public')->assertExists($imagePath);
        
        // التحقق من أن الصورة تم إنشاؤها في قاعدة البيانات
        $this->assertDatabaseHas('product_images', [
            'product_id' => $this->product->id,
            'image_path' => $imagePath,
            'is_primary' => true,
        ]);
    }
    
    /** @test */
    public function admin_users_can_upload_multiple_product_images()
    {
        // إنشاء ملفات وهمية للتحميل
        $file1 = UploadedFile::fake()->image('product1.jpg');
        $file2 = UploadedFile::fake()->image('product2.jpg');
        
        // اختبار أن المستخدم المشرف يمكنه تحميل صور متعددة للمنتج
        $response = $this->actingAs($this->adminUser)
                         ->postJson("/api/products/{$this->product->id}/images/multiple", [
                             'images' => [$file1, $file2],
                         ]);
        
        $response->assertStatus(200)
                 ->assertJsonCount(2);
        
        // التحقق من أن الصور تم إنشاؤها في قاعدة البيانات
        $this->assertEquals(2, $this->product->images()->count());
    }
    
    /** @test */
    public function admin_users_can_set_primary_image()
    {
        // إنشاء صور للمنتج
        $image1 = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'is_primary' => true,
        ]);
        
        $image2 = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'is_primary' => false,
        ]);
        
        // اختبار أن المستخدم المشرف يمكنه تعيين الصورة الرئيسية
        $response = $this->actingAs($this->adminUser)
                         ->getJson("/api/products/{$this->product->id}/images/{$image2->id}/set-primary");
        
        $response->assertStatus(200);
        
        // التحقق من أن الصورة الثانية أصبحت الصورة الرئيسية
        $this->assertDatabaseHas('product_images', [
            'id' => $image2->id,
            'is_primary' => true,
        ]);
        
        // التحقق من أن الصورة الأولى لم تعد الصورة الرئيسية
        $this->assertDatabaseHas('product_images', [
            'id' => $image1->id,
            'is_primary' => false,
        ]);
    }
    
    /** @test */
    public function admin_users_can_update_images_order()
    {
        // إنشاء صور للمنتج
        $image1 = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'sort_order' => 0,
        ]);
        
        $image2 = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'sort_order' => 1,
        ]);
        
        $image3 = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'sort_order' => 2,
        ]);
        
        // اختبار أن المستخدم المشرف يمكنه تحديث ترتيب الصور
        $response = $this->actingAs($this->adminUser)
                         ->postJson("/api/products/{$this->product->id}/images/order", [
                             'images_order' => [
                                 0 => $image3->id,
                                 1 => $image1->id,
                                 2 => $image2->id,
                             ],
                         ]);
        
        $response->assertStatus(200);
        
        // التحقق من أن ترتيب الصور تم تحديثه
        $this->assertDatabaseHas('product_images', [
            'id' => $image3->id,
            'sort_order' => 0,
        ]);
        
        $this->assertDatabaseHas('product_images', [
            'id' => $image1->id,
            'sort_order' => 1,
        ]);
        
        $this->assertDatabaseHas('product_images', [
            'id' => $image2->id,
            'sort_order' => 2,
        ]);
    }
    
    /** @test */
    public function admin_users_can_delete_product_image()
    {
        // إنشاء صورة للمنتج
        $image = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'image_path' => 'products/test.jpg',
        ]);
        
        // إنشاء ملف وهمي في التخزين
        Storage::disk('public')->put('products/test.jpg', 'test content');
        
        // اختبار أن المستخدم المشرف يمكنه حذف صورة المنتج
        $response = $this->actingAs($this->adminUser)
                         ->deleteJson("/api/products/{$this->product->id}/images/{$image->id}");
        
        $response->assertStatus(200);
        
        // التحقق من أن الصورة تم حذفها من قاعدة البيانات
        $this->assertDatabaseMissing('product_images', [
            'id' => $image->id,
        ]);
        
        // التحقق من أن الملف تم حذفه من التخزين
        Storage::disk('public')->assertMissing('products/test.jpg');
    }
    
    /** @test */
    public function regular_users_cannot_upload_product_images()
    {
        // إنشاء ملف وهمي للتحميل
        $file = UploadedFile::fake()->image('product.jpg');
        
        // اختبار أن المستخدم العادي لا يمكنه تحميل صورة للمنتج
        $response = $this->actingAs($this->regularUser)
                         ->postJson("/api/products/{$this->product->id}/images", [
                             'image' => $file,
                         ]);
        
        $response->assertStatus(403); // Forbidden
    }
}