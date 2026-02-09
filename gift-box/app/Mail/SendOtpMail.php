<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $email,
        public string $otp,
        public string $name = 'User'
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your OTP Code for GiftBox Registration',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
            with: [
                'email' => $this->email,
                'otp' => $this->otp,
                'name' => $this->name,
                'expiresIn' => '10 minutes',
            ],
        );
    }
}
