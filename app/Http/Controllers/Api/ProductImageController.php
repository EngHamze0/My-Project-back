<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductImageResource;
use App\Services\ProductImageService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductImageController extends Controller
{
    protected $productImageService;

    public function __construct(ProductImageService $productImageService)
    {
        $this->productImageService = $productImageService;
    }

    /**
     * الحصول على جميع صور المنتج
     */
    public function index($productId)
    {
        try {
            $images = $this->productImageService->getProductImages($productId);
            return ProductImageResource::collection($images);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * تحميل صورة واحدة للمنتج
     */
    public function store(Request $request, $productId)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'is_primary' => 'boolean',
                'sort_order' => 'nullable|integer|min:0',
            ]);

            $image = $this->productImageService->uploadProductImage($request, $productId);
            return new ProductImageResource($image);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * تحميل صور متعددة للمنتج
     */
    public function storeMultiple(Request $request, $productId)
    {
        try {
            $request->validate([
                'images' => 'required|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $images = $this->productImageService->uploadMultipleProductImages($request, $productId);
            return ProductImageResource::collection($images);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * تعيين الصورة الرئيسية للمنتج
     */
    public function setPrimary($productId, $imageId)
    {
        try {
            $image = $this->productImageService->setPrimaryImage($productId, $imageId);
            return new ProductImageResource($image);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * تحديث ترتيب الصور
     */
    public function updateOrder(Request $request, $productId)
    {
        try {
            $request->validate([
                'images_order' => 'required|array',
                'images_order.*' => 'integer|exists:product_images,id',
            ]);

            $images = $this->productImageService->updateImagesOrder($request, $productId);
            return ProductImageResource::collection($images);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * حذف صورة المنتج
     */
    public function destroy($productId, $imageId)
    {
        try {
            $result = $this->productImageService->deleteProductImage($productId, $imageId);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
} 