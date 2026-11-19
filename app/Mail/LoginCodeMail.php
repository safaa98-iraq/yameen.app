<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class LoginCodeMail extends Mailable
{
    use Queueable;

    public function __construct(public string $code, public string $mailLocale)
    {
        $this->locale($mailLocale);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('ui.auth.mail_subject', ['code' => $this->code], $this->mailLocale));
    }

    public function content(): Content
    {
        return new Content(view: 'mail.login-code');
    }
}
