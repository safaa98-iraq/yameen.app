<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\WaylEvent;
use App\Services\Payments;
use App\Services\Wayl\WaylClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/** Hourly: ask Wayl about orders still pending 30 minutes to 48 hours after creation. */
class WaylReconcile extends Command
{
    protected $signature = 'wayl:reconcile';

    protected $description = 'Check pending Wayl orders and apply payments the webhook missed';

    public function handle(WaylClient $wayl, Payments $payments): int
    {
        if (! $wayl->configured()) {
            $this->warn('Wayl is not configured.');

            return self::SUCCESS;
        }

        $orders = Order::where('status', 'pending')
            ->whereBetween('created_at', [now()->subHours(48), now()->subMinutes(30)])
            ->get();

        foreach ($orders as $order) {
            try {
                $raw = $wayl->getLink($order->id);
            } catch (\Throwable $e) {
                $this->error("{$order->id}: {$e->getMessage()}");

                continue;
            }

            // Log one raw response so the field names can be confirmed against Wayl's docs.
            if (Cache::add('wayl:reconcile:sample-logged', true, now()->addYears(10))) {
                Log::info('Wayl link response sample (reconcile)', ['response' => $raw]);
            }

            $status = WaylClient::paymentStatus($raw);
            $result = $status === 'Paid' ? $payments->markPaid($order->id, WaylClient::code($raw)) : 'pending:'.($status ?? 'unknown');
            WaylEvent::create([
                'source' => 'reconcile',
                'reference_id' => $order->id,
                'payload' => json_encode($raw, JSON_UNESCAPED_UNICODE),
                'processed_at' => now(),
                'result' => $result,
            ]);
            $this->line("{$order->id}: {$result}");
        }

        return self::SUCCESS;
    }
}
