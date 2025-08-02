<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    //
    protected $fillable = [
        'name',
        'photo',
        'video',
        'description',
        'start_date',
        'end_date',
        'location',
        'organizer',
        'contact_email',
        'contact_phone',
        'website',
        'social_media',
        'category',
        'tags',
        'status',
        'visibility',
        'accessibility',
        'latitude',
        'longitude'
    ];
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'status' => 'string',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    protected $appends = [
        'formatted_start_date',
        'formatted_end_date',
    ];
    public function getFormattedStartDateAttribute()
    {
        return $this->start_date->format('d-m-Y');
    }
    public function getFormattedEndDateAttribute()
    {
        return $this->end_date->format('d-m-Y');
    }
    public function getPhotoUrlAttribute()
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }
    public function getVideoUrlAttribute()
    {
        return $this->video ? asset('storage/' . $this->video) : null;
    }
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
    public function getWebsiteUrlAttribute()
    {
        return $this->website ? url($this->website) : null;
    }
    public function getSocialMediaUrlAttribute()
    {
        return $this->social_media ? url($this->social_media) : null;
    }
    public function getLocationUrlAttribute()
    {
        return $this->latitude && $this->longitude ? 'https://www.google.com/maps/search/?api=1&query=' . $this->latitude . ',' . $this->longitude : null;
    }
    public function getContactEmailUrlAttribute()
    {
        return $this->contact_email ? 'mailto:' . $this->contact_email : null;
    }
    public function getContactPhoneUrlAttribute()
    {
        return $this->contact_phone ? 'tel:' . $this->contact_phone : null;
    }
    public function getCategoryAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }
    public function setCategoryAttribute($value)
    {
        $this->attributes['category'] = is_array($value) ? implode(',', $value) : $value;
    }
    public function getTagsAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }
    public function setTagsAttribute($value)
    {
        $this->attributes['tags'] = is_array($value) ? implode(',', $value) : $value;
    }
    public function getStatusAttribute($value)
    {
        // Ensure status is always returned as a string
        if (is_array($value)) {
            return implode(', ', $value);
        }
        
        return $value;
    }
    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = is_array($value) ? implode(',', $value) : $value;
    }
    public function getVisibilityAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }
    public function setVisibilityAttribute($value)
    {
        $this->attributes['visibility'] = is_array($value) ? implode(',', $value) : $value;
    }
    public function getAccessibilityAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }
    public function setAccessibilityAttribute($value)
    {
        $this->attributes['accessibility'] = is_array($value) ? implode(',', $value) : $value;
    }

    #BelongsToMany
    public function users()
    {
        return $this->belongsToMany(User::class, 'event_user')->withTimestamps();
    }
}
