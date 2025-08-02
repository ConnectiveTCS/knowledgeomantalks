<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordSend extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $url;
    public $password;

    public function __construct(User $user, $url, $password)
    {
        $this->user = $user;
        $this->url = $url;
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject('Reset Your Password')
            ->markdown('emails.attendees.password-send')
            ->with([
                'user' => $this->user,
                'url' => $this->url,
                'password' => $this->password,
            ]);
    }
}
