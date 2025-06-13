<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    /**
     * إنشاء طلب جديد من مصفوفة معرفات المنتجات
     */
    public function createOrder($userId, $productsData, $couponCode = null)
    {
        try {
            DB::beginTransaction();

            // إنشاء الطلب
            $order = new Order([
                'user_id' => $userId,
                'order_number' => Order::generateOrderNumber(),
                'status' => 'pending',
                'subtotal' => 0,
                'discount' => 0,
                'total' => 0,
                'coupon_code' => $couponCode,
                'payment_method' => 'cash', // يمكن تغييره حسب متطلبات التطبيق
                'payment_status' => 'pending',
            ]);
            $order->save();

            $subtotal = 0;

            // إضافة عناصر الطلب
            foreach ($productsData as $productData) {
                $productId = $productData['product_id'];
                $quantity = $productData['quantity'] ?? 1;

                // التحقق من وجود المنتج
                $product = Product::findOrFail($productId);

                // حساب السعر الإجمالي للعنصر
                $totalPrice = $product->price * $quantity;
                $subtotal += $totalPrice;

                // إنشاء عنصر الطلب
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'total_price' => $totalPrice,
                    'product_details' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'price' => $product->price,
                        'image' => $product->image,
                    ],
                ]);
            }

            // تحديث إجماليات الطلب
            $order->subtotal = $subtotal;
            $order->total = $subtotal;
            $discount = 0;

            // تطبيق الكوبون إذا كان موجوداً
            if ($couponCode) {
                $coupon = Coupon::where('code', $couponCode)
                    ->where('is_active', true)
                    ->first();

                if ($coupon && $coupon->isValid($subtotal)) {
                    $discount = $coupon->calculateDiscount($subtotal);
                    $order->discount = $discount;
                    $order->total = $subtotal - $discount;
                    
                    // زيادة عدد مرات استخدام الكوبون
                    $coupon->incrementUsage();
                } else {
                    // إذا كان الكوبون غير صالح، إزالته من الطلب
                    $order->coupon_code = null;
                }
            }

            $order->save();

            DB::commit();

            return $order->load('items.product');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * الحصول على طلب محدد
     */
    public function getOrder($orderId, $userId = null)
    {
        $query = Order::with('items.product');
        
        // إذا تم تحديد معرف المستخدم، فسيتم التحقق من أن الطلب ينتمي إلى هذا المستخدم
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query->findOrFail($orderId);
    }

    /**
     * الحصول على قائمة طلبات المستخدم
     */
    public function getUserOrders($userId)
    {
        return Order::with('items')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    /**
     * الحصول على جميع الطلبات (للمشرف)
     */
    public function getAllOrders()
    {
        return Order::with('user', 'items')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    /**
     * تحديث حالة الطلب
     */
    public function updateOrderStatus($orderId, $status)
    {
        try {
            DB::beginTransaction();

            $order = Order::findOrFail($orderId);
            $order->status = $status;
            $order->save();

            DB::commit();

            return $order->load('items.product');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
} 