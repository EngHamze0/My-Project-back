<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImageService
{
    /**
     * تحميل صورة للمنتج
     */
    public function uploadProductImage(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        
        // التحقق من وجود صورة في الطلب
        if (!$request->hasFile('image')) {
            throw new \Exception('لم يتم تحميل أي صورة', 400);
        }
        
        $image = $request->file('image');
        
        // التحقق من صحة الصورة
        if (!$image->isValid()) {
            throw new \Exception('الصورة غير صالحة', 400);
        }
        
        // تخزين الصورة
        $path = $image->store('products', 'public');
        
        // تحديد ما إذا كانت هذه الصورة الرئيسية
        $isPrimary = $request->boolean('is_primary', false);
        
        // إذا كانت هذه الصورة الرئيسية، قم بإلغاء تعيين الصور الرئيسية الأخرى
        if ($isPrimary) {
            $product->images()->where('is_primary', true)->update(['is_primary' => false]);
        }
        // إذا كانت هذه أول صورة للمنتج، اجعلها الصورة الرئيسية
        elseif ($product->images()->count() === 0) {
            $isPrimary = true;
        }
        
        // تحديد ترتيب الصورة
        $sortOrder = $request->input('sort_order', $product->images()->max('sort_order') + 1);
        
        // إنشاء سجل الصورة
        $productImage = $product->images()->create([
            'image_path' => $path,
            'is_primary' => $isPrimary,
            'sort_order' => $sortOrder,
        ]);
        
        return $productImage;
    }
    
    /**
     * تحميل صور متعددة للمنتج
     */
    public function uploadMultipleProductImages(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        
        // التحقق من وجود صور في الطلب
        if (!$request->hasFile('images')) {
            throw new \Exception('لم يتم تحميل أي صور', 400);
        }
        
        $images = $request->file('images');
        $uploadedImages = [];
        
        // تحديد أعلى ترتيب موجود
        $maxSortOrder = $product->images()->max('sort_order') ?: 0;
        
        // هل هناك صورة رئيسية موجودة بالفعل
        $hasPrimaryImage = $product->images()->where('is_primary', true)->exists();
        
        foreach ($images as $index => $image) {
            // التحقق من صحة الصورة
            if (!$image->isValid()) {
                continue;
            }
            
            // تخزين الصورة
            $path = $image->store('products', 'public');
            
            // تحديد ما إذا كانت هذه الصورة الرئيسية
            $isPrimary = false;
            
            // إذا كانت هذه أول صورة وليس هناك صورة رئيسية موجودة بالفعل
            if ($index === 0 && !$hasPrimaryImage) {
                $isPrimary = true;
            }
            
            // إنشاء سجل الصورة
            $productImage = $product->images()->create([
                'image_path' => $path,
                'is_primary' => $isPrimary,
                'sort_order' => $maxSortOrder + $index + 1,
            ]);
            
            $uploadedImages[] = $productImage;
        }
        
        return $uploadedImages;
    }
    
    /**
     * تعيين الصورة الرئيسية للمنتج
     */
    public function setPrimaryImage($productId, $imageId)
    {
        $product = Product::findOrFail($productId);
        
        // التحقق من أن الصورة تنتمي للمنتج
        $image = $product->images()->findOrFail($imageId);
        
        // إلغاء تعيين الصور الرئيسية الأخرى
        $product->images()->where('is_primary', true)->update(['is_primary' => false]);
        
        // تعيين الصورة الجديدة كصورة رئيسية
        $image->update(['is_primary' => true]);
        
        return $image;
    }
    
    /**
     * تحديث ترتيب الصور
     */
    public function updateImagesOrder(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        
        // التحقق من وجود بيانات الترتيب
        if (!$request->has('images_order')) {
            throw new \Exception('لم يتم توفير بيانات الترتيب', 400);
        }
        
        $imagesOrder = $request->input('images_order');
        
        // التحقق من أن البيانات عبارة عن مصفوفة
        if (!is_array($imagesOrder)) {
            throw new \Exception('بيانات الترتيب غير صالحة', 400);
        }
        
        // تحديث ترتيب الصور
        foreach ($imagesOrder as $order => $imageId) {
            $product->images()->where('id', $imageId)->update(['sort_order' => $order]);
        }
        
        return $product->images()->orderBy('sort_order')->get();
    }
    
    /**
     * حذف صورة المنتج
     */
    public function deleteProductImage($productId, $imageId)
    {
        $product = Product::findOrFail($productId);
        
        // التحقق من أن الصورة تنتمي للمنتج
        $image = $product->images()->findOrFail($imageId);
        
        // حذف الصورة من التخزين
        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }
        
        // حذف سجل الصورة
        $image->delete();
        
        // إذا كانت الصورة المحذوفة هي الصورة الرئيسية، قم بتعيين صورة أخرى كصورة رئيسية
        if ($image->is_primary && $product->images()->count() > 0) {
            $product->images()->first()->update(['is_primary' => true]);
        }
        
        return ['message' => 'تم حذف الصورة بنجاح'];
    }
    
    /**
     * الحصول على جميع صور المنتج
     */
    public function getProductImages($productId)
    {
        $product = Product::findOrFail($productId);
        return $product->images()->orderBy('sort_order')->get();
    }
} 