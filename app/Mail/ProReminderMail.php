<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ProReminderMail extends Mailable
{
    public function __construct(public User $user)
    {
        $this->locale(config('yameen.default_locale'));
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('ui.reminder.subject'));
    }

    public function content(): Content
    {
        return new Content(view: 'mail.pro-reminder', with: [
            'date' => $this->user->pro_until->timezone(config('yameen.credits_timezone'))->format('Y-m-d'),
            'url' => lroute('pricing', [], config('yameen.default_locale')),
        ]);
    }
}
