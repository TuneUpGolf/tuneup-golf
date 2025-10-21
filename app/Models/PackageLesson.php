<?php

namespace App\Models;

use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackageLesson extends Model
{
    use BelongsToTenant;
    //product
    protected $table = "lesson_package";
    protected $guard_name = 'web';


    protected $fillable = [
        'tenant_id',
        'lesson_id',
        'number_of_slot',
        'price'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function lesson(): HasMany
    {
        return $this->hasMany(Lesson::class, 'lesson_id');
    }
}
