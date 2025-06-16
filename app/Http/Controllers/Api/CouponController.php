<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class CouponController extends Controller
{
   

    /**
     * عرض قائمة الكوبونات
     */
    public function index()
    {
        try {
            $coupons = Coupon::latest()->paginate(10);
            
            return response()->json([
                'status' => 'success',
                'message' => 'تم الحصول على قائمة الكوبونات بنجاح',
                'data' => $coupons
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * تخزين كوبون جديد
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:coupons,code',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات غير صالحة',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $coupon = Coupon::create($request->all());
            
            return response()->json([
                'status' => 'success',
                'message' => 'تم إنشاء الكوبون بنجاح',
                'data' => $coupon
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * عرض تفاصيل كوبون محدد
     */
    public function show($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'message' => 'تم الحصول على تفاصيل الكوبون بنجاح',
                'data' => $coupon
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * تحديث كوبون محدد
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'sometimes|required|string|max:50|unique:coupons,code,' . $id,
            'type' => 'sometimes|required|in:percentage,fixed',
            'value' => 'sometimes|required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات غير صالحة',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $coupon = Coupon::findOrFail($id);
            $coupon->update($request->all());
            
            return response()->json([
                'status' => 'success',
                'message' => 'تم تحديث الكوبون بنجاح',
                'data' => $coupon
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * حذف كوبون محدد
     */
    public function destroy($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);
            $coupon->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'تم حذف الكوبون بنجاح'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * التحقق من صلاحية كوبون
     */
    public function validateCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|string|max:50',
            'subtotal' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات غير صالحة',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $coupon = Coupon::where('code', $request->coupon_code)
                ->where('is_active', true)
                ->first();

            if (!$coupon) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'الكوبون غير صالح'
                ], 400);
            }

            if (!$coupon->isValid($request->subtotal)) {
                $reason = '';
                
                if ($request->subtotal < $coupon->min_order_amount) {
                    $reason = 'قيمة الطلب أقل من الحد الأدنى المطلوب';
                } elseif ($coupon->max_uses && $coupon->used_times >= $coupon->max_uses) {
                    $reason = 'تم استخدام الكوبون الحد الأقصى من المرات';
                } else {
                    $reason = 'الكوبون غير صالح أو منتهي الصلاحية';
                }
                
                return response()->json([
                    'status' => 'error',
                    'message' => $reason
                ], 400);
            }

            $discount = $coupon->calculateDiscount($request->subtotal);
            
            return response()->json([
                'status' => 'success',
                'message' => 'الكوبون صالح',
                'data' => [
                    'coupon' => $coupon,
                    'discount_amount' => $discount,
                    'total_after_discount' => $request->subtotal - $discount
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 