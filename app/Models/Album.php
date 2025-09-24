<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;
    protected $table = 'albums';
    protected $fillable = [
        'user_id',
        'tenant_id',
        'album_category_id',
        'title',
        'description',
        'media',
        'status',
        'slug'
    ];

}