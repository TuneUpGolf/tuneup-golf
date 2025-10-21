<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Payment;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Purchase extends Model
{
    use BelongsToTenant;
    //sales
    protected $table = "purchases";

    protected $fillable = [
        'id',
        'tenant_id',
        'student_id',
        'instructor_id',
        'coupon_id',
        'lesson_id', // ammount of purchase can be deduced from lesson_id
        'isFeedbackComplete',
        'slot_id',
        'total_amount',
        'friend_names',
        'type',
        'purchased_slot',
        'status'
    ];
    protected $guarded = [
        'status', // Completed or not
        'lessons_used' //lesson quantity - used (lesson_quantity - purchase_videos->count());
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const STATUS_INCOMPLETE = "incomplete";
    const STATUS_COMPLETE = "complete";

    const STATUS_MAPPING = [
        "incomplete" => "Incomplete",
        "complete"  => "Completed",
        "inprogress" => "InComplete",
    ];

    public function student()
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(Slots::class, 'id', 'slot_id');
    }

    public function videos()
    {
        return $this->hasMany(PurchaseVideos::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
    // Check if the slot is fully booked
    public function isFullyBooked(): mixed
    {
        $bookedSlotCount = \App\Models\StudentSlot::where('student_id', $this->student_id)
            ->whereHas('slot', function ($query) {
                $query->where('lesson_id', $this->lesson_id);
            })->count();

        return $bookedSlotCount < $this->purchased_slot;
    }

    public function getIsDeletedAttribute()
{
    return strip_tags($this->deleted) === 'Deleted';
}

}