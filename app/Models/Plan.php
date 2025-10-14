<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'plans';

    protected $fillable = [
        'name',
        'price',
        'duration',
        'max_users',
        'max_roles',
        'max_instructors',
        'max_documents',
        'max_blogs',
        'discount',
        'durationtype',
        'description',
        'tenant_id',
        'active_status',
        'is_chat_enabled',
        'discount_setting',
        'instructor_id',
        'stripe_product_id',
        'stripe_price_id',
        'stripe_webhook_id',
        'lesson_limit'
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id', 'id')->where('created_by', '>', 0);
    }

    /**
     * Encrypt the plan ID
     *
     * @return string
     */
    public function getEncryptedIdAttribute()
    {
        return Crypt::encrypt($this->id);
    }

    public function getLessonLimitLabelAttribute()
    {
        return match ($this->lesson_limit) {
            -1 => 'Unlimited lessons/month',
            default => "{$this->lesson_limit} lessons/month",
        };
    }
}
