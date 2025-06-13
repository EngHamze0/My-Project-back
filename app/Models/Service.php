<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_days',
        'is_active'
    ];

    /**
     * Get the subscriptions for the service.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the users subscribed to this service.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'subscriptions')
            ->withPivot('start_date', 'end_date', 'amount_paid', 'status', 'notes')
            ->withTimestamps();
    }
} 