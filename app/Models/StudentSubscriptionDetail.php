<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSubscriptionDetail extends Model
{
    use HasFactory;

    protected $table = 'student_subscription_details';

    protected $fillable = [
        'student_subscription_id',
        'invoice_id',
        'payment_intent_id'
    ];

    public function StudentSubscription(){
        return $this->belongsTo(StudentSubscription::class);
    }
}
