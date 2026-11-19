<?php

namespace App\Jobs;

use App\Models\WaylEvent;
use App\Services\Payments;
use App\Services\Wayl\WaylClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWaylEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public int $eventId) {}

    public function handle(Payments $payments): void
    {
        $event = WaylEvent::find($this->eventId);
        if (! $event || $event->processed_at) {
            return;
        }

        $data = $event->data();
        $status = WaylClient::paymentStatus($data);
        $reference = WaylClient::referenceId($data);

        $result = ($status === 'Paid' && $reference)
            ? $payments->markPaid($reference, WaylClient::code($data))
            : 'ignored:'.($status ?? 'no-status');

        $event->update(['processed_at' => now(), 'result' => $result]);
    }
}
