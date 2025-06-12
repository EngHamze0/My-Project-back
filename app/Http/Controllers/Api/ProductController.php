<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        try {
            $products = $this->productService->getAllProducts($request);
            return new ProductCollection($products);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(ProductRequest $request)
    {
        try {
            $product = $this->productService->createProduct($request);
            return new ProductResource($product);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        try {
            $product = $this->productService->getProduct($id);
            return new ProductResource($product);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    /**
     * Update the specified product in storage.
     */
    public function update(ProductRequest $request, $id)
    {
        try {
            $product = $this->productService->updateProduct($request, $id);
            return new ProductResource($product);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy($id)
    {
        try {
            $result = $this->productService->deleteProduct($id);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Toggle product status (active/inactive).
     */
    public function toggleStatus($id)
    {
        try {
            $product = $this->productService->toggleProductStatus($id);
            return new ProductResource($product);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
} 