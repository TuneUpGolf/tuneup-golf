<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstructorSubscriptionDetail extends Model
{
    use HasFactory;

     protected $table = 'instructor_subscription_details';

    protected $fillable = [
        'instructor_subscription_id',
        'invoice_id',
        'payment_intent_id'
    ];
}
