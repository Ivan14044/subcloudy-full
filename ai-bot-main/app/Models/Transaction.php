<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'currency',
        'payment_method',
        'subscription_id',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
