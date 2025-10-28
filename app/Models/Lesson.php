<?php

namespace App\Models;

use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    use BelongsToTenant;
    //product
    protected $table = "lessons";
    protected $guard_name = 'web';

    const LESSON_TYPE_INPERSON = 'inPerson';
    const LESSON_TYPE_ONLINE = 'online';
    const LESSON_PAYMENT_CASH = 'cash';
    const LESSON_PAYMENT_ONLINE = 'online';
    const LESSON_PAYMENT_BOTH = 'both';
    const LESSON_TYPE_PACKAGE = 'package';

    const TYPE_MAPPING = [
        "package"  => "In-Person & Package Lessons",
        "online" => "Online Lessons",
        // "package" => "Package",
        'inPerson' => 'In-Person & Package Lessons'
    ];

    const SELECT_TYPE_MAPPING = [
        "package"  => "In-Person & Package Lessons",
        "online" => "Online Lessons",
        // "package" => "Package",
        'inPerson' => 'Pre-Set Date Lessons'
    ];

    // const TYPE_MAPPING = [
    //     "inPerson"  => "In-Person",
    //     "online" => "Online",
    //     "package" => "Package",
    //     'pre-set' => 'Pre-Set Date'
    // ];


    protected $fillable = [
        'lesson_name',
        'lesson_description',
        'lesson_price',
        'lesson_quantity', // student can upload video, instructor will provide feedback. This field will decide how many videos will the instructor give feedback on in the given price. 
        'required_time',
        'created_by',
        'detailed_description',
        'active_status',
        'type',
        'payment_method',
        'lesson_duration',
        'max_students',
        'is_package_lesson',
        'package_lesson_type',
        'number_of_slots',
        'long_description',
        'logo',
        'column_order',
        'advance_booking_limit_days',
        'last_minute_booking_buffer_hours',
        'cancel_window_hours',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(Slots::class, 'lesson_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'lesson_id');
    }
    public function packages(): HasMany
    {
        return $this->hasMany(PackageLesson::class, 'lesson_id');
    }
}
