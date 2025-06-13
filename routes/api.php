<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminUserApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductImageController;
use App\Http\Middleware\CheckAdminRole;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/test', function () {
    return response()->json(['message' => 'Hello World'] ,201);
});

// مسارات المصادقة
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);



// مسارات المنتجات للمستخدمين العاديين (قراءة فقط)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);



// مسارات تتطلب المصادقة
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    
    // مسارات صور المنتجات للمستخدمين العاديين (قراءة فقط)
    Route::get('/products/{productId}/images', [ProductImageController::class, 'index']);
});

// مسارات المنتجات للمشرفين
Route::middleware(['auth:sanctum', CheckAdminRole::class])->group(function () {
    // مسارات المستخدمين
    Route::apiResource('users', AdminUserApiController::class);
    Route::post('users/{id}', [AdminUserApiController::class, 'update']);
    Route::get('users/toggle-active/{id}', [AdminUserApiController::class, 'toggleActive']);
    
    // مسارات المنتجات (إنشاء، تعديل، حذف)
    Route::post('/products', [ProductController::class, 'store']);
    Route::post('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::get('/products/toggle-status/{id}', [ProductController::class, 'toggleStatus']);
    
    // مسارات صور المنتجات
    Route::post('/products/{productId}/images', [ProductImageController::class, 'store']);
    Route::post('/products/{productId}/images/multiple', [ProductImageController::class, 'storeMultiple']);
    Route::post('/products/{productId}/images/order', [ProductImageController::class, 'updateOrder']);
    Route::get('/products/{productId}/images/{imageId}/set-primary', [ProductImageController::class, 'setPrimary']);
    Route::delete('/products/{productId}/images/{imageId}', [ProductImageController::class, 'destroy']);
});





