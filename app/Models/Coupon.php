<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_uses',
        'used_times',
        'start_date',
        'end_date',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * العلاقة مع الطلبات
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'coupon_code', 'code');
    }

    /**
     * التحقق من صلاحية الكوبون
     */
    public function isValid($subtotal = 0)
    {
        // التحقق من حالة التنشيط
        if (!$this->is_active) {
            return false;
        }

        // التحقق من تاريخ الصلاحية
        $today = Carbon::today();
        if ($this->start_date && $today->lt($this->start_date)) {
            return false;
        }
        if ($this->end_date && $today->gt($this->end_date)) {
            return false;
        }

        // التحقق من عدد مرات الاستخدام
        if ($this->max_uses && $this->used_times >= $this->max_uses) {
            return false;
        }

        // التحقق من الحد الأدنى للطلب
        if ($subtotal < $this->min_order_amount) {
            return false;
        }

        return true;
    }

    /**
     * حساب قيمة الخصم
     */
    public function calculateDiscount($subtotal)
    {
        if ($this->type === 'percentage') {
            return ($subtotal * $this->value) / 100;
        } else { // fixed
            return min($subtotal, $this->value); // لا يمكن أن يكون الخصم أكبر من قيمة الطلب
        }
    }

    /**
     * زيادة عدد مرات استخدام الكوبون
     */
    public function incrementUsage()
    {
        $this->increment('used_times');
        return $this;
    }
} 