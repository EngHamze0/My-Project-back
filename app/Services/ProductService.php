<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Services\ProductImageService;
use Illuminate\Support\Facades\DB;

class ProductService
{
    protected $productImageService;
    
    public function __construct(ProductImageService $productImageService)
    {
        $this->productImageService = $productImageService;
    }
    
    /**
     * Get all products or filter by type
     */
    public function getAllProducts(Request $request)
    {
        $query = Product::query();
        
        // Filter by type if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Include images if requested
        if ($request->boolean('with_images', false)) {
            $query->with('images');
        }
        
        // Include primary image if requested
        if ($request->boolean('with_primary_image', true)) {
            $query->with('primaryImage');
        }
        
        // Sort products
        $query->orderBy($request->get('sort_by', 'created_at'), $request->get('sort_direction', 'desc'));
        
        return $query->paginate($request->get('per_page', 15));
    }
    
    /**
     * Get a specific product by ID
     */
    public function getProduct($id, $withImages = true)
    {
        $query = Product::query();
        
        if ($withImages) {
            $query->with(['images', 'primaryImage']);
        }
        
        $product = $query->findOrFail($id);
        return $product;
    }
    
    /**
     * Create a new product
     */
    public function createProduct($request)
    {
        DB::beginTransaction();
        try {
            // Create the product with validated data
            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'quantity' => $request->quantity,
                'type' => $request->type,
                'specifications' => $request->specifications,
                'status' => $request->status ?? 'active',
            ]);
            
            // إذا كان هناك صور، قم بتحميلها
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                $primaryImageIndex = $request->input('primary_image_index', 0);
                
                foreach ($images as $index => $image) {
                    // تخزين الصورة
                    $path = $image->store('products', 'public');
                    
                    // تحديد ما إذا كانت هذه الصورة الرئيسية
                    $isPrimary = ($index == $primaryImageIndex);
                    
                    // إنشاء سجل الصورة
                    $product->images()->create([
                        'image_path' => $path,
                        'is_primary' => $isPrimary,
                        'sort_order' => $index + 1,
                    ]);
                }
            }
            
            DB::commit();
            return $product->load('images');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Update an existing product
     */
    public function updateProduct($request, $id)
    {
        DB::beginTransaction();
        try {
            $product = Product::findOrFail($id);
            
            // Update the product with validated data
            $product->update([
                'name' => $request->name ?? $product->name,
                'description' => $request->description ?? $product->description,
                'price' => $request->price ?? $product->price,
                'quantity' => $request->quantity ?? $product->quantity,
                'type' => $request->type ?? $product->type,
                'specifications' => $request->specifications ?? $product->specifications,
                'status' => $request->status ?? $product->status,
            ]);
            
            // إذا كان هناك صور، قم بتحميلها
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                $primaryImageIndex = $request->input('primary_image_index', 0);
                
                foreach ($images as $index => $image) {
                    // تخزين الصورة
                    $path = $image->store('products', 'public');
                    
                    // تحديد ما إذا كانت هذه الصورة الرئيسية
                    $isPrimary = ($index == $primaryImageIndex);
                    
                    // إذا كانت هذه الصورة الرئيسية، قم بإلغاء تعيين الصور الرئيسية الأخرى
                    if ($isPrimary) {
                        $product->images()->where('is_primary', true)->update(['is_primary' => false]);
                    }
                    
                    // إنشاء سجل الصورة
                    $product->images()->create([
                        'image_path' => $path,
                        'is_primary' => $isPrimary,
                        'sort_order' => $product->images()->max('sort_order') + $index + 1,
                    ]);
                }
            }
            
            DB::commit();
            return $product->load('images');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Delete a product
     */
    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        
        // حذف الصور المرتبطة بالمنتج (سيتم حذفها تلقائيًا بسبب onDelete('cascade') في المهاجرة)
        // لكن يجب حذف الملفات الفعلية من التخزين
        foreach ($product->images as $image) {
            if (\Storage::disk('public')->exists($image->image_path)) {
                \Storage::disk('public')->delete($image->image_path);
            }
        }
        
        $product->delete();
        
        return ['message' => 'تم حذف المنتج بنجاح'];
    }
    
    /**
     * Toggle product status (active/inactive)
     */
    public function toggleProductStatus($id)
    {
        $product = Product::findOrFail($id);
        $product->status = $product->status === 'active' ? 'inactive' : 'active';
        $product->save();
        
        return $product;
    }
    
    /**
     * Validate specifications based on product type
     */
    private function validateSpecifications(Request $request)
    {
        $type = $request->type;
        $specifications = $request->specifications ?? [];
        
        switch ($type) {
            case 'battery':
                $validator = Validator::make(['specifications' => $specifications], [
                    'specifications.capacity' => 'required|numeric|min:0',
                    'specifications.voltage' => 'required|numeric|min:0',
                    'specifications.chemistry' => 'required|string',
                    'specifications.cycle_life' => 'nullable|numeric|min:0',
                    'specifications.dimensions' => 'nullable|string',
                    'specifications.weight' => 'nullable|numeric|min:0',
                    'specifications.brand' => 'nullable|string',
                ]);
                break;
                
            case 'solar_panel':
                $validator = Validator::make(['specifications' => $specifications], [
                    'specifications.power' => 'required|numeric|min:0',
                    'specifications.voltage' => 'required|numeric|min:0',
                    'specifications.current' => 'required|numeric|min:0',
                    'specifications.dimensions' => 'nullable|string',
                    'specifications.weight' => 'nullable|numeric|min:0',
                    'specifications.cell_type' => 'nullable|string',
                    'specifications.efficiency' => 'nullable|numeric|min:0|max:100',
                    'specifications.brand' => 'nullable|string',
                ]);
                break;
                
            case 'inverter':
                $validator = Validator::make(['specifications' => $specifications], [
                    'specifications.power' => 'required|numeric|min:0',
                    'specifications.input_voltage' => 'required|numeric|min:0',
                    'specifications.output_voltage' => 'required|numeric|min:0',
                    'specifications.efficiency' => 'nullable|numeric|min:0|max:100',
                    'specifications.dimensions' => 'nullable|string',
                    'specifications.weight' => 'nullable|numeric|min:0',
                    'specifications.type' => 'nullable|string',
                    'specifications.brand' => 'nullable|string',
                ]);
                break;
                
            default:
                throw ValidationException::withMessages([
                    'type' => ['نوع المنتج غير صالح'],
                ]);
        }
        
        if ($validator && $validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }
    }
} 