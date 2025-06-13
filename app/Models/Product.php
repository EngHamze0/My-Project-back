<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity',
        'type', // 'battery', 'solar_panel', 'inverter'
        'specifications', // JSON field for specific attributes
        'status', // 'active', 'inactive'
    ];

    protected $casts = [
        'specifications' => 'array',
        'price' => 'float',
    ];

    /**
     * العلاقة مع صور المنتج
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * الحصول على الصورة الرئيسية للمنتج
     */
    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * العلاقة مع المفضلات
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * التحقق مما إذا كان المنتج مفضلاً لدى مستخدم معين
     */
    public function isFavorite($userId = null)
    {
        if (!$userId && auth()->check()) {
            $userId = auth()->id();
        }
        
        if (!$userId) {
            return false;
        }
        
        return $this->favorites()->where('user_id', $userId)->exists();
    }

    // Scope to filter by product type
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Get batteries
    public function scopeBatteries($query)
    {
        return $query->where('type', 'battery');
    }

    // Get solar panels
    public function scopeSolarPanels($query)
    {
        return $query->where('type', 'solar_panel');
    }

    // Get inverters
    public function scopeInverters($query)
    {
        return $query->where('type', 'inverter');
    }
} 