<?php

use App\Http\Controllers\API\CouponController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminUserApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
// use App\Http\Controllers\Api\ProductImageController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\API\OrderController;
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

// مسارات الخدمات للجميع (قراءة فقط)
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{id}', [ServiceController::class, 'show']);

// مسارات تتطلب المصادقة
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // مسارات اشتراكات المستخدم الحالي
    Route::get('/my-subscriptions', [SubscriptionController::class, 'mySubscriptions']);
    Route::get('/subscriptions/{id}', [SubscriptionController::class, 'show']);
    Route::get('/subscriptions/{id}/cancel', [SubscriptionController::class, 'cancel']);
    
    // اشتراك المستخدم في خدمة
    Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
    
    // مسارات المفضلات
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']); // we don't used
    Route::delete('/favorites/{productId}', [FavoriteController::class, 'destroy']);// we don't used
    Route::post('/favorites/toggle/{productId}', [FavoriteController::class, 'toggle']);
    Route::get('/favorites/check/{productId}', [FavoriteController::class, 'check']);// we don't used

    // مسارات الكوبونات للمستخدمين
    Route::post('/coupons/validate', [App\Http\Controllers\API\CouponController::class, 'validateCoupon']);
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
    
    // مسارات الخدمات (إنشاء، تعديل، حذف)
    Route::post('/services', [ServiceController::class, 'store']);
    Route::post('/services/{id}', [ServiceController::class, 'update']);
    Route::delete('/services/{id}', [ServiceController::class, 'destroy']);
    Route::get('/services/toggle-status/{id}', [ServiceController::class, 'toggleStatus']);
    Route::get('/services/{id}/subscribers', [ServiceController::class, 'subscribers']);
    
    // مسارات الاشتراكات (للمشرفين)
    Route::get('/subscriptions', [SubscriptionController::class, 'index']);
    Route::post('/subscriptions', [SubscriptionController::class, 'store']);
    Route::post('/subscriptions/{id}', [SubscriptionController::class, 'update']);
    Route::post('/subscriptions/{id}/renew', [SubscriptionController::class, 'renew']);
    
    // مسارات لوحة التحكم
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/users', [DashboardController::class, 'usersStats']);
    Route::get('/dashboard/services', [DashboardController::class, 'servicesStats']);
    Route::get('/dashboard/subscriptions', [DashboardController::class, 'subscriptionsStats']);
    Route::get('/dashboard/orders', [DashboardController::class, 'ordersStats']);

    // مسارات الكوبونات للمشرفين
    Route::apiResource('/coupons', CouponController::class);
    Route::post('/coupons/update/{id}', [CouponController::class, 'update']);//update copuon
    Route::post('/coupons/validateCoupon', [CouponController::class, 'validateCoupon']);//update copuon

});

// مسارات الطلبات للمستخدمين
Route::middleware('auth:sanctum')->prefix('orders')->group(function () {
    Route::post('/', [OrderController::class, 'store']); // إنشاء طلب جديد
    Route::get('/user', [OrderController::class, 'getUserOrders']); // الحصول على طلبات المستخدم
    Route::get('/user/{orderId}', [OrderController::class, 'getUserOrder']); // الحصول على تفاصيل طلب محدد
});

// مسارات الطلبات للمشرفين
Route::middleware(['auth:sanctum', CheckAdminRole::class])->prefix('admin/orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']); // الحصول على جميع الطلبات
    Route::get('/{orderId}', [OrderController::class, 'show']); // الحصول على تفاصيل طلب محدد
    Route::post('/{orderId}/status', [OrderController::class, 'updateStatus']); // تحديث حالة الطلب
});

// // مسارات صور المنتجات
// Route::post('/products/{productId}/images', [ProductImageController::class, 'store']);
// Route::post('/products/{productId}/images/multiple', [ProductImageController::class, 'storeMultiple']);
// Route::post('/products/{productId}/images/order', [ProductImageController::class, 'updateOrder']);
// Route::get('/products/{productId}/images/{imageId}/set-primary', [ProductImageController::class, 'setPrimary']);
// Route::delete('/products/{productId}/images/{imageId}', [ProductImageController::class, 'destroy']);
// مسارات صور المنتجات للمستخدمين العاديين (قراءة فقط)
// Route::get('/products/{productId}/images', [ProductImageController::class, 'index']);
 




