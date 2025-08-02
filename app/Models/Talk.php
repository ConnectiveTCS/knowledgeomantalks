<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Talk extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'speaker_id',
        'event_id',
        'duration',
        'start_time',
        'end_time',
        'status',
        'video_url',
        'slides_url',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Get the speaker for this talk.
     */
    public function speaker()
    {
        return $this->belongsTo(Speaker::class);
    }

    /**
     * Get the event for this talk.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
