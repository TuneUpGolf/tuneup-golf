<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstructorBlockSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'start_time',
        'end_time',
        'description'
    ];

    protected $casts = [
        'start_time' => 'datetime:Y-m-d H:i:s',
        'end_time'   => 'datetime:Y-m-d H:i:s',
    ];


    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
}
