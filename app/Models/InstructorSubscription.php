<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstructorSubscription extends Model
{
    use HasFactory;

    protected $table = 'instructor_subscriptions';

    protected $fillable = [
        'plan_id',
        'instructor_id',
        'tenant_id',
        'stripe_customer_id',
        'stripe_subscription_id',
        'status',
    ];
}
