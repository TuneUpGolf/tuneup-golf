<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class StudentSlot extends Model
{

    protected $table = "student_slots";

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function slot()
    {
        return $this->belongsTo(Slots::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
