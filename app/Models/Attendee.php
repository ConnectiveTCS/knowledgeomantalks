<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendee extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'country',
        'city',
        'address',
        'postal_code'
    ];

    /**
     * Get the events that the attendee has registered for.
     */
    public function events()
    {
        return $this->belongsToMany(Event::class, 'attendee_event');
    }
}
