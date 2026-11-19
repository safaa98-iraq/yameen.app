<?php

namespace App\Console\Commands;

use App\Mail\ProReminderMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/** Daily: email users whose Pro ends in about 3 days (once per expiry date). */
class ProRemind extends Command
{
    protected $signature = 'pro:remind';

    protected $description = 'Send renewal reminders 3 days before Pro ends';

    public function handle(): int
    {
        $users = User::whereBetween('pro_until', [now()->addDays(2), now()->addDays(3)])->get()
            ->filter(fn (User $u) => ! $u->pro_reminder_sent_for || ! $u->pro_reminder_sent_for->equalTo($u->pro_until));

        foreach ($users as $user) {
            Mail::to($user->email)->send(new ProReminderMail($user));
            $user->forceFill(['pro_reminder_sent_for' => $user->pro_until])->save();
        }
        $this->info("Sent {$users->count()} reminders.");

        return self::SUCCESS;
    }
}
