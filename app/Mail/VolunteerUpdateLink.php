<?php

namespace App\Mail;

use App\Models\Volunteer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VolunteerUpdateLink extends Mailable
{
    use Queueable, SerializesModels;

    public $volunteer;
    public $url;

    public function __construct(Volunteer $volunteer, $url)
    {
        $this->volunteer = $volunteer;
        $this->url = $url;
    }

    public function build()
    {
        return $this->subject('Complete or Update Your Volunteer Profile')
            ->markdown('emails.volunteer.update-link');
    }
}
