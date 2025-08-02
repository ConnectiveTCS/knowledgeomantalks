<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Speaker extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'bio',
        'company',
        'position',
        'industry',
        'photo',
        'user_id',
        'CV_Resume',
        'website',
        'linkedin',
        'twitter',
        'facebook',
        'instagram',
        'youtube',
        'tiktok',
        'is_featured',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'social_links' => 'array',
    ];

    /**
     * Get the talks for the speaker.
     */
    public function talks()
    {
        return $this->hasMany(Talk::class);
    }

    /**
     * Get the user that owns the speaker profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
