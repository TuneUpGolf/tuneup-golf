<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Slots extends Model
{
    use BelongsToTenant;

    protected $table = 'slots';
    protected $guard_name = 'web';
    public $timestamps = false;
    protected $date_time = ['datetime_column'];
    protected $with = ['student'];

    protected $fillable = [
        'lesson_id',
        'student_id',
        'date_time',
        'location',
        'is_completed',
        'is_active',
        'cancelled'
    ];

    public function lesson()
    {
        return $this->belongsTo(\App\Models\Lesson::class, 'lesson_id');
    }
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
