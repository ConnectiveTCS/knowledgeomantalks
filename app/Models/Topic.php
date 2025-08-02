<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    //
    protected $fillable = [
        'foreign_id',
        'title',
        'slug',
        'description',
        'image',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
    public function getImagePathAttribute()
    {
        return $this->image ? storage_path('app/public/' . $this->image) : null;
    }
    public function getImageNameAttribute()
    {
        return $this->image ? basename($this->image) : null;
    }
    public function getImageExtensionAttribute()
    {
        return $this->image ? pathinfo($this->image, PATHINFO_EXTENSION) : null;
    }
    public function getImageSizeAttribute()
    {
        return $this->image ? filesize($this->getImagePathAttribute()) : null;
    }
    public function getImageMimeTypeAttribute()
    {
        return $this->image ? mime_content_type($this->getImagePathAttribute()) : null;
    }
    public function getImageDimensionsAttribute()
    {
        return $this->image ? getimagesize($this->getImagePathAttribute()) : null;
    }
    public function getImageWidthAttribute()
    {
        return $this->image ? $this->getImageDimensionsAttribute()[0] : null;
    }
    public function getImageHeightAttribute()
    {
        return $this->image ? $this->getImageDimensionsAttribute()[1] : null;
    }

    #BelongsToOneSpeaker
    public function speaker()
    {
        return $this->belongsTo(Speaker::class);
    }
    #HasManySpeakers
    public function speakers()
    {
        return $this->hasMany(Speaker::class);
    }
}
