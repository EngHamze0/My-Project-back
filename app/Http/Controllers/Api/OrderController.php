<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderCollection;
use Illuminate\Http\Request;
use Exception;
use Validator;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
        // $this->middleware('auth:sanctum');
    }

    /**
     * إنشاء طلب جديد
     */
    public function store(Request $request)
    {
            $validated = Validator::make($request->all(),[
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'sometimes|integer|min:1',
            'coupon_code' => 'nullable|string'
        ]);
        if($validated->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors()
            ], 422);
        }
        try {
            $userId = auth('sanctum')->user()->id;
            $order = $this->orderService->createOrder(
                $userId,
                $request->products,
                $request->coupon_code
            );

            return response()->json([
                'status' => 'success',
                'message' => 'تم إنشاء الطلب بنجاح',
                'data' => new OrderResource($order)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على قائمة طلبات المستخدم الحالي
     */
    public function getUserOrders()
    {
        try {
            $userId = auth('sanctum')->user()->id;
            $orders = $this->orderService->getUserOrders($userId);

            return response()->json([
                'status' => 'success',
                'message' => 'تم الحصول على قائمة الطلبات بنجاح',
                'data' => new OrderCollection($orders)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على تفاصيل طلب محدد للمستخدم الحالي
     */
    public function getUserOrder($orderId)
    {
        try {
            $userId = auth('sanctum')->user()->id;
            $order = $this->orderService->getOrder($orderId, $userId);

            return response()->json([
                'status' => 'success',
                'message' => 'تم الحصول على تفاصيل الطلب بنجاح',
                'data' => new OrderResource($order)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * الحصول على جميع الطلبات (للمشرف)
     */
    public function index()
    {
        try {
            // التحقق من أن المستخدم مشرف
            if (auth('sanctum')->user()->role !== 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'غير مصرح لك بالوصول إلى هذه البيانات'
                ], 403);
            }

            $orders = $this->orderService->getAllOrders();

            return response()->json([
                'status' => 'success',
                'message' => 'تم الحصول على قائمة الطلبات بنجاح',
                'data' => new OrderCollection($orders)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على تفاصيل طلب محدد (للمشرف)
     */
    public function show($orderId)
    {
        try {
            // التحقق من أن المستخدم مشرف
            if (auth('sanctum')->user()->role !== 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'غير مصرح لك بالوصول إلى هذه البيانات'
                ], 403);
            }

            $order = $this->orderService->getOrder($orderId);

            return response()->json([
                'status' => 'success',
                'message' => 'تم الحصول على تفاصيل الطلب بنجاح',
                'data' => new OrderResource($order)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * تحديث حالة الطلب (للمشرف)
     */
    public function updateStatus(Request $request, $orderId)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled,refunded'
        ]);

        try {
            // التحقق من أن المستخدم مشرف
            if (auth('sanctum')->user()->role !== 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'غير مصرح لك بتحديث حالة الطلب'
                ], 403);
            }

            $order = $this->orderService->updateOrderStatus($orderId, $request->status);

            return response()->json([
                'status' => 'success',
                'message' => 'تم تحديث حالة الطلب بنجاح',
                'data' => new OrderResource($order)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }
} 