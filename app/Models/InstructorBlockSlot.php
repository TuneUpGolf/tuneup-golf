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

    public function instructor(){
        return $this->belongsTo(User::class, 'instructor_id');
    }
}
