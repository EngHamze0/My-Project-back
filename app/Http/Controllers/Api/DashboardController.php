<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * عرض إحصائيات لوحة التحكم الرئيسية.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // التحقق من صلاحيات المستخدم
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'غير مصرح لك بالوصول إلى لوحة التحكم'
            ], 403);
        }

        // إحصائيات المستخدمين
        $usersCount = User::count();
        $activeUsersCount = User::where('is_active', true)->count();
        $inactiveUsersCount = User::where('is_active', false)->count();

        // إحصائيات الخدمات
        $servicesCount = Service::count();
        $activeServicesCount = Service::where('is_active', true)->count();

        // إحصائيات الاشتراكات
        $subscriptionsCount = Subscription::count();
        $activeSubscriptionsCount = Subscription::where('status', 'active')->count();
        $expiredSubscriptionsCount = Subscription::where('status', 'expired')->count();
        $cancelledSubscriptionsCount = Subscription::where('status', 'cancelled')->count();

        // إحصائيات المنتجات (إذا كانت موجودة)
        $productsCount = class_exists('App\Models\Product') ? Product::count() : 0;

        // إجمالي الإيرادات من الاشتراكات
        $totalRevenue = Subscription::sum('amount_paid');
        
        // إيرادات الشهر الحالي
        $currentMonthRevenue = Subscription::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount_paid');

        // الخدمات الأكثر اشتراكاً
        $topServices = Service::withCount('subscriptions')
            ->orderBy('subscriptions_count', 'desc')
            ->take(5)
            ->get();

        // اشتراكات الشهر الحالي
        $currentMonthSubscriptions = Subscription::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // إحصائيات الاشتراكات خلال الـ 6 أشهر الماضية
        $last6MonthsStats = $this->getSubscriptionStatsForLast6Months();

        return response()->json([
            'status' => 'success',
            'data' => [
                'users' => [
                    'total' => $usersCount,
                    'active' => $activeUsersCount,
                    'inactive' => $inactiveUsersCount
                ],
                'services' => [
                    'total' => $servicesCount,
                    'active' => $activeServicesCount,
                    'inactive' => $servicesCount - $activeServicesCount
                ],
                'subscriptions' => [
                    'total' => $subscriptionsCount,
                    'active' => $activeSubscriptionsCount,
                    'expired' => $expiredSubscriptionsCount,
                    'cancelled' => $cancelledSubscriptionsCount,
                    'current_month' => $currentMonthSubscriptions
                ],
                'products' => [
                    'total' => $productsCount
                ],
                'revenue' => [
                    'total' => $totalRevenue,
                    'current_month' => $currentMonthRevenue
                ],
                'top_services' => $topServices,
                'subscription_trends' => $last6MonthsStats
            ]
        ]);
    }

    /**
     * الحصول على إحصائيات الاشتراكات للـ 6 أشهر الماضية.
     *
     * @return array
     */
    private function getSubscriptionStatsForLast6Months()
    {
        $stats = [];
        $now = Carbon::now();

        // الحصول على إحصائيات لكل شهر من الـ 6 أشهر الماضية
        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $month = $date->format('m');
            $year = $date->format('Y');
            $monthName = $date->translatedFormat('F'); // اسم الشهر بالعربية إذا كانت اللغة معدة للعربية

            $count = Subscription::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->count();

            $revenue = Subscription::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->sum('amount_paid');

            $stats[] = [
                'month' => $monthName,
                'count' => $count,
                'revenue' => $revenue
            ];
        }

        return $stats;
    }

    /**
     * عرض إحصائيات المستخدمين.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function usersStats()
    {
        // التحقق من صلاحيات المستخدم
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'غير مصرح لك بالوصول إلى لوحة التحكم'
            ], 403);
        }

        // إحصائيات المستخدمين
        $usersCount = User::count();
        $activeUsersCount = User::where('is_active', true)->count();
        $inactiveUsersCount = User::where('is_active', false)->count();

        // المستخدمين الجدد خلال الشهر الحالي
        $newUsersThisMonth = User::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // المستخدمين الجدد خلال الأسبوع الحالي
        $newUsersThisWeek = User::whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ])->count();

        // المستخدمين الأكثر اشتراكاً
        $topSubscribers = User::withCount('subscriptions')
            ->orderBy('subscriptions_count', 'desc')
            ->take(10)
            ->get(['id', 'name', 'email', 'phone']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'total' => $usersCount,
                'active' => $activeUsersCount,
                'inactive' => $inactiveUsersCount,
                'new_this_month' => $newUsersThisMonth,
                'new_this_week' => $newUsersThisWeek,
                'top_subscribers' => $topSubscribers
            ]
        ]);
    }

    /**
     * عرض إحصائيات الخدمات.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function servicesStats()
    {
        // التحقق من صلاحيات المستخدم
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'غير مصرح لك بالوصول إلى لوحة التحكم'
            ], 403);
        }

        // إحصائيات الخدمات
        $servicesCount = Service::count();
        $activeServicesCount = Service::where('is_active', true)->count();
        $inactiveServicesCount = Service::where('is_active', false)->count();

        // الخدمات الأكثر اشتراكاً
        $topServices = Service::withCount('subscriptions')
            ->orderBy('subscriptions_count', 'desc')
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'price' => $service->price,
                    'duration_days' => $service->duration_days,
                    'is_active' => $service->is_active,
                    'subscriptions_count' => $service->subscriptions_count,
                    'total_revenue' => $service->subscriptions()->sum('amount_paid')
                ];
            });

        // الخدمات الأعلى إيراداً
        $topRevenueServices = $topServices->sortByDesc('total_revenue')->values()->take(5);

        return response()->json([
            'status' => 'success',
            'data' => [
                'total' => $servicesCount,
                'active' => $activeServicesCount,
                'inactive' => $inactiveServicesCount,
                'top_subscribed' => $topServices->take(5),
                'top_revenue' => $topRevenueServices
            ]
        ]);
    }

    /**
     * عرض إحصائيات الاشتراكات.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function subscriptionsStats()
    {
        // التحقق من صلاحيات المستخدم
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'غير مصرح لك بالوصول إلى لوحة التحكم'
            ], 403);
        }

        // إحصائيات الاشتراكات
        $subscriptionsCount = Subscription::count();
        $activeSubscriptionsCount = Subscription::where('status', 'active')->count();
        $expiredSubscriptionsCount = Subscription::where('status', 'expired')->count();
        $cancelledSubscriptionsCount = Subscription::where('status', 'cancelled')->count();

        // إجمالي الإيرادات
        $totalRevenue = Subscription::sum('amount_paid');

        // إيرادات الشهر الحالي
        $currentMonthRevenue = Subscription::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount_paid');

        // إيرادات السنة الحالية
        $currentYearRevenue = Subscription::whereYear('created_at', Carbon::now()->year)
            ->sum('amount_paid');

        // الاشتراكات التي ستنتهي قريباً (خلال الشهر القادم)
        $soonToExpire = Subscription::where('status', 'active')
            ->whereBetween('end_date', [
                Carbon::now(),
                Carbon::now()->addMonth()
            ])
            ->with(['user', 'service'])
            ->get();

        // إحصائيات الاشتراكات حسب الشهر للسنة الحالية
        $monthlyStats = $this->getMonthlySubscriptionStats();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total' => $subscriptionsCount,
                'active' => $activeSubscriptionsCount,
                'expired' => $expiredSubscriptionsCount,
                'cancelled' => $cancelledSubscriptionsCount,
                'revenue' => [
                    'total' => $totalRevenue,
                    'current_month' => $currentMonthRevenue,
                    'current_year' => $currentYearRevenue
                ],
                'soon_to_expire' => $soonToExpire,
                'monthly_stats' => $monthlyStats
            ]
        ]);
    }

    /**
     * الحصول على إحصائيات الاشتراكات الشهرية للسنة الحالية.
     *
     * @return array
     */
    private function getMonthlySubscriptionStats()
    {
        $stats = [];
        $currentYear = Carbon::now()->year;

        for ($month = 1; $month <= 12; $month++) {
            $date = Carbon::createFromDate($currentYear, $month, 1);
            $monthName = $date->translatedFormat('F'); // اسم الشهر بالعربية إذا كانت اللغة معدة للعربية

            $count = Subscription::whereMonth('created_at', $month)
                ->whereYear('created_at', $currentYear)
                ->count();

            $revenue = Subscription::whereMonth('created_at', $month)
                ->whereYear('created_at', $currentYear)
                ->sum('amount_paid');

            $stats[] = [
                'month' => $monthName,
                'count' => $count,
                'revenue' => $revenue
            ];
        }

        return $stats;
    }
}