<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    /**
     * عرض قائمة الخدمات.
     */
    public function index()
    {
        // إذا كان المستخدم مشرف، نعرض جميع الخدمات
        // وإلا نعرض فقط الخدمات النشطة
        $services = auth()->user() && auth()->user()->role === 'admin'
            ? Service::all()
            : Service::where('is_active', true)->get();

        return response()->json([
            'status' => 'success',
            'data' => $services
        ]);
    }

    /**
     * إنشاء خدمة جديدة.
     */
    public function store(Request $request)
    {
        // التحقق من صحة البيانات
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات غير صالحة',
                'errors' => $validator->errors()
            ], 422);
        }

        // إنشاء الخدمة
        $service = Service::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'duration_days' => $request->duration_days ?? 365,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم إنشاء الخدمة بنجاح',
            'data' => $service
        ], 201);
    }

    /**
     * عرض تفاصيل خدمة معينة.
     */
    public function show($id)
    {
        $service = Service::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $service
        ]);
    }

    /**
     * تحديث خدمة معينة.
     */
    public function update(Request $request, $id)
    {
        // التحقق من وجود الخدمة
        $service = Service::findOrFail($id);

        // التحقق من صحة البيانات
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'duration_days' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات غير صالحة',
                'errors' => $validator->errors()
            ], 422);
        }

        // تحديث الخدمة
        $service->update([
            'name' => $request->name ?? $service->name,
            'description' => $request->description ?? $service->description,
            'price' => $request->price ?? $service->price,
            'duration_days' => $request->duration_days ?? $service->duration_days,
            'is_active' => $request->has('is_active') ? $request->is_active : $service->is_active,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث الخدمة بنجاح',
            'data' => $service
        ]);
    }

    /**
     * حذف خدمة معينة.
     */
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        
        // التحقق مما إذا كانت الخدمة مرتبطة باشتراكات نشطة
        $activeSubscriptions = $service->subscriptions()->where('status', 'active')->count();
        
        if ($activeSubscriptions > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'لا يمكن حذف الخدمة لأنها مرتبطة باشتراكات نشطة'
            ], 422);
        }
        
        $service->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف الخدمة بنجاح'
        ]);
    }

    /**
     * تبديل حالة الخدمة (نشطة/غير نشطة).
     */
    public function toggleStatus($id)
    {
        $service = Service::findOrFail($id);
        $service->is_active = !$service->is_active;
        $service->save();

        return response()->json([
            'status' => 'success',
            'message' => 'تم تغيير حالة الخدمة بنجاح',
            'data' => $service
        ]);
    }

    /**
     * عرض المستخدمين المشتركين في خدمة معينة.
     */
    public function subscribers($id)
    {
        $service = Service::findOrFail($id);
       
        $subscribers = $service->subscriptions()->with('user')->get()->map(function ($subscription) {
            return [
                'subscription_id' => $subscription->id,
                'user' => $subscription->user,
                'start_date' => $subscription->start_date,
                'end_date' => $subscription->end_date,
                'status' => $subscription->status,
                'amount_paid' => $subscription->amount_paid,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $subscribers
        ]);
    }
} 