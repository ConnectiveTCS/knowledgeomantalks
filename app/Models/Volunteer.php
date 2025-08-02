<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Volunteer extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'city',
        'skills',
        'availability',
        'interests',
        'cv_resume',
        'photo',
        'notes',
        'active',
        'status',
        'referral_source',
        'background_check_status',
        'background_check_date',
        'background_check_notes',
        'training_status',
        'training_date',
        'training_notes',
        'orientation_status',
        'orientation_date',
        'orientation_notes',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'medical_conditions',
        'allergies',
        'languages',
        'hours'
    ];

    protected $casts = [
        'active' => 'boolean',
        'background_check_date' => 'datetime',
        'training_date' => 'datetime',
        'orientation_date' => 'datetime',
    ];

    protected $dates = [
        'background_check_date',
        'training_date',
        'orientation_date',
    ];

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getStatusLabelAttribute()
    {
        return [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ][$this->status] ?? 'Unknown';
    }

    #BelongsToManyUser
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_volunteer', 'volunteer_id', 'user_id');
    }

    #HasManyUser
    public function user()
    {
        return $this->hasMany(User::class, 'volunteer_id', 'id');
    }

    /**
     * Get the events that the volunteer has worked at.
     */
    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_volunteer');
    }
}
