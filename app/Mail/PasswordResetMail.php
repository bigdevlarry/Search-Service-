<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $email)
    {}

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this->subject('Password Reset Request')
            ->view('emails.password_reset')
            ->with([
                'email' => $this->email,
                'resetLink' => url('/v1/password/reset'),
            ]);
    }
}
