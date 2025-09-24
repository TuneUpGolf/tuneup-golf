<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlbumCategory extends Model
{
    use HasFactory;
    protected $table = 'album_categories';
    protected $fillable = [
        'instructor_id',
        'tenant_id',
        'title',
        'slug',
        'description',
        'payment_mode',
        'price',
        'image',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class);
    }
}