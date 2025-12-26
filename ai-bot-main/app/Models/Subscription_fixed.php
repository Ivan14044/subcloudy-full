<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SubscriptionLog;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'payment_method',
        'service_id',
        'is_trial',
        'is_auto_renew',
        'expiring_email_sent',
        'next_payment_at',
        'order_id',
        'payment_attempts',
    ];
