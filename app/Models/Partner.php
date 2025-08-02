<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_name',
        'contact_name',
        'contact_email',
        'contact_phone',
        'website',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'description',
        'logo',
        'status',
        'type',
        'category',
        'sub_category',
        'partnership_level',
        'partnership_start_date',
        'partnership_end_date',
        'partnership_renewal_date',
        'partnership_renewal_status',
        'partnership_renewal_notes',
        'partnership_renewal_approval',
        'partnership_renewal_approval_notes',
        'partnership_renewal_approval_date',
        'partnership_renewal_approval_user',
    ];

    protected $casts = [
        'partnership_start_date' => 'datetime',
        'partnership_end_date' => 'datetime',
        'partnership_renewal_date' => 'datetime',
        'partnership_renewal_approval_date' => 'datetime',
    ];

    /**
     * Get the events sponsored by this partner.
     */
    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_partner');
    }
}
