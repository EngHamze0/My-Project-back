<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    /**
     * عرض قائمة الاشتراكات للمستخدم الحالي أو للمشرف.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // إذا كان المستخدم مشرف، يمكنه عرض جميع الاشتراكات أو اشتراكات مستخدم معين
        if ($user->role === 'admin') {
            $userId = $request->input('user_id');
            
            if ($userId) {
                $subscriptions = Subscription::where('user_id', $userId)
                    ->with(['service', 'user'])
                    ->get();
            } else {
                $subscriptions = Subscription::with(['service', 'user'])->get();
            }
        } else {
            // المستخدم العادي يمكنه فقط عرض اشتراكاته
            $subscriptions = $user->subscriptions()->with('service')->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $subscriptions
        ]);
    }

    /**
     * إنشاء اشتراك جديد.
     */
    public function store(Request $request)
    {
        // التحقق من صحة البيانات
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'start_date' => 'nullable|date',
            'amount_paid' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات غير صالحة',
                'errors' => $validator->errors()
            ], 422);
        }

        // التحقق من أن المستخدم موجود ونشط
        $user = User::findOrFail($request->user_id);
        if (!$user->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'المستخدم غير نشط'
            ], 422);
        }

        // التحقق من أن الخدمة موجودة ونشطة
        $service = Service::findOrFail($request->service_id);
        if (!$service->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'الخدمة غير نشطة'
            ], 422);
        }

        // حساب تاريخ البدء والانتهاء
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::today();
        $endDate = $startDate->copy()->addDays($service->duration_days);

        // إنشاء الاشتراك
        $subscription = Subscription::create([
            'user_id' => $request->user_id,
            'service_id' => $request->service_id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'amount_paid' => $request->amount_paid,
            'status' => 'active',
            'notes' => $request->notes,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم إنشاء الاشتراك بنجاح',
            'data' => $subscription->load(['service', 'user'])
        ], 201);
    }

    /**
     * عرض تفاصيل اشتراك معين.
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $subscription = Subscription::with(['service', 'user'])->findOrFail($id);
        
        // التحقق من أن المستخدم مشرف أو صاحب الاشتراك
        if ($user->role !== 'admin' && $subscription->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'غير مصرح لك بعرض هذا الاشتراك'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $subscription
        ]);
    }

    /**
     * تحديث اشتراك معين.
     */
    public function update(Request $request, $id)
    {
        // فقط المشرف يمكنه تحديث الاشتراكات
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'غير مصرح لك بتحديث الاشتراكات'
            ], 403);
        }

        // التحقق من وجود الاشتراك
        $subscription = Subscription::findOrFail($id);

        // التحقق من صحة البيانات
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'amount_paid' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,expired,cancelled',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات غير صالحة',
                'errors' => $validator->errors()
            ], 422);
        }

        // تحديث الاشتراك
        $subscription->update([
            'start_date' => $request->has('start_date') ? Carbon::parse($request->start_date) : $subscription->start_date,
            'end_date' => $request->has('end_date') ? Carbon::parse($request->end_date) : $subscription->end_date,
            'amount_paid' => $request->amount_paid ?? $subscription->amount_paid,
            'status' => $request->status ?? $subscription->status,
            'notes' => $request->notes ?? $subscription->notes,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث الاشتراك بنجاح',
            'data' => $subscription->load(['service', 'user'])
        ]);
    }

    /**
     * إلغاء اشتراك معين.
     */
    public function cancel($id)
    {
        $user = Auth::user();
        
        $subscription = Subscription::findOrFail($id);
        
        // التحقق من أن المستخدم مشرف أو صاحب الاشتراك
        if ($user->role !== 'admin' && $subscription->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'غير مصرح لك بإلغاء هذا الاشتراك'
            ], 403);
        }

        // تغيير حالة الاشتراك إلى ملغي
        $subscription->status = 'cancelled';
        $subscription->save();

        return response()->json([
            'status' => 'success',
            'message' => 'تم إلغاء الاشتراك بنجاح',
            'data' => $subscription
        ]);
    }
    
    /**
     * تجديد اشتراك معين.
     */
    public function renew(Request $request, $id)
    {
        // فقط المشرف يمكنه تجديد الاشتراكات
        // if (Auth::user()->role !== 'admin') {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'غير مصرح لك بتجديد الاشتراكات'
        //     ], 403);
        // }

        $subscription = Subscription::with('service')->findOrFail($id);
        
        // التحقق من صحة البيانات
        $validator = Validator::make($request->all(), [
            'amount_paid' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات غير صالحة',
                'errors' => $validator->errors()
            ], 422);
        }

        $newStartDate = Carbon::today();
        $newEndDate = $newStartDate->copy()->addDays($subscription->service->duration_days);

        // تجديد الاشتراك
        $subscription->update([
            'start_date' => $newStartDate,
            'end_date' => $newEndDate,
            'amount_paid' => $request->amount_paid ?? $subscription->service->price,
            'status' => 'active',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تجديد الاشتراك بنجاح',
            'data' => $subscription->load(['service', 'user'])
        ]);
    }

    /**
     * عرض اشتراكات المستخدم الحالي.
     */
    public function mySubscriptions()
    {
        $user = Auth::user();
        $subscriptions = $user->subscriptions()->with('service')->get();

        return response()->json([
            'status' => 'success',
            'data' => $subscriptions
        ]);
    }

    /**
     * اشتراك المستخدم الحالي في خدمة معينة.
     */
    public function subscribe(Request $request)
    {
        $user = Auth::user();
        
        // التحقق من صحة البيانات
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'payment_details' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات غير صالحة',
                'errors' => $validator->errors()
            ], 422);
        }

        // التحقق من أن الخدمة موجودة ونشطة
        $service = Service::findOrFail($request->service_id);
        if (!$service->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'الخدمة غير متاحة حالياً'
            ], 422);
        }

        // التحقق مما إذا كان المستخدم مشترك بالفعل في هذه الخدمة بشكل نشط
        $activeSubscription = $user->subscriptions()
            ->where('service_id', $service->id)
            ->where('status', 'active')
            ->where('end_date', '>', Carbon::today())
            ->first();

        if ($activeSubscription) {
            return response()->json([
                'status' => 'error',
                'message' => 'أنت مشترك بالفعل في هذه الخدمة',
                'data' => $activeSubscription
            ], 422);
        }

        // حساب تاريخ البدء والانتهاء
        $startDate = Carbon::today();
        $endDate = $startDate->copy()->addDays($service->duration_days);

        // إنشاء الاشتراك
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'amount_paid' => $service->price,
            'status' => 'active',
            'notes' => 'اشتراك مباشر من المستخدم. ' . ($request->payment_details ?? ''),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم الاشتراك في الخدمة بنجاح',
            'data' => $subscription->load('service')
        ], 201);
    }
} 