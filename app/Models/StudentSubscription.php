<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSubscription extends Model
{
    use HasFactory;

    protected $table = 'student_subscriptions';

    protected $fillable = [
        'student_id',
        'plan_id',
        'instructor_id',
        'tenant_id',
        'stripe_customer_id',
        'stripe_subscription_id',
        'status',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
