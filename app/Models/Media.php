<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    //
    protected $table = 'media';
    protected $fillable = [
        'title',
        'slug',
        'type',
        'url',
        'thumbnail',
        'description',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function getRouteKeyName()
    {
        return 'slug';
    }
    public function getUrlAttribute($value)
    {
        return $value ? asset($value) : null;
    }
    public function getThumbnailAttribute($value)
    {
        return $value ? asset($value) : null;
    }
}
