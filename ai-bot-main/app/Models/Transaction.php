<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'user_id',
        'amount',
        'currency',
        'payment_method',
        'subscription_id',
        'status',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function getStatusLabelAttribute(): string
    {
        $status = $this->status ?? self::STATUS_PENDING;
        return match ($status) {
            self::STATUS_PENDING => __('admin.transaction_status.pending'),
            self::STATUS_COMPLETED => __('admin.transaction_status.completed'),
            self::STATUS_FAILED => __('admin.transaction_status.failed'),
            self::STATUS_REFUNDED => __('admin.transaction_status.refunded'),
            default => $status,
        };
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', self::STATUS_REFUNDED);
    }
}
