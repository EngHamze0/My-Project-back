<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'subtotal',
        'discount',
        'total',
        'coupon_code',
        'payment_method',
        'payment_status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العلاقة مع عناصر الطلب
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * توليد رقم طلب فريد
     */
    public static function generateOrderNumber()
    {
        $prefix = 'ORD-';
        $timestamp = now()->format('YmdHis');
        $randomStr = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 5);
        
        return $prefix . $timestamp . '-' . $randomStr;
    }
} 