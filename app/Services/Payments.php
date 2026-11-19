<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Refund;
use App\Models\Setting;
use App\Models\User;
use App\Services\Wayl\WaylClient;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class Payments
{
    public function __construct(private readonly WaylClient $wayl) {}

    /** amount_iqd = ceil(price_usd × rate / 250) × 250 */
    public static function amountIqd(float $priceUsd, float $rate): int
    {
        return (int) (ceil(round($priceUsd * $rate, 6) / 250) * 250);
    }

    public const PLANS = ['monthly', 'lifetime'];

    /**
     * Plan price and duration come from admin settings. The lifetime plan is
     * paid once and has no duration (days = 0).
     */
    public static function plan(string $plan): array
    {
        if (! in_array($plan, self::PLANS, true)) {
            throw new InvalidArgumentException('Unknown plan');
        }
        $price = (float) Setting::get('plan_'.$plan.'_price');

        return [
            'price_usd' => $price,
            'days' => $plan === 'lifetime' ? 0 : (int) Setting::get('plan_'.$plan.'_days'),
            'amount_iqd' => self::amountIqd($price, (float) Setting::get('usd_to_iqd_rate')),
        ];
    }

    /** Create a pending order and its Wayl payment link. */
    public function checkout(User $user, string $plan, string $label, string $webhookUrl, callable $redirectUrl): Order
    {
        $p = self::plan($plan);
        $order = Order::create([
            'user_id' => $user->id,
            'plan' => $plan,
            'days' => $p['days'],
            'price_usd' => $p['price_usd'],
            'rate_used' => (float) Setting::get('usd_to_iqd_rate'),
            'amount_iqd' => $p['amount_iqd'],
            'status' => 'pending',
        ]);

        $link = $this->wayl->createLink($order, $label, $webhookUrl, $redirectUrl($order));
        $order->update(['checkout_url' => $link['url']]);

        return $order;
    }

    /**
     * Mark an order paid and extend Pro. Idempotent: an order that is no longer
     * pending is left alone, so the same referenceId twice is a no-op.
     * Extends from the current pro_until when Pro is still active.
     */
    public function markPaid(string $orderId, ?string $waylCode = null): string
    {
        return DB::transaction(function () use ($orderId, $waylCode) {
            $order = Order::whereKey($orderId)->lockForUpdate()->first();
            if (! $order) {
                return 'unknown-order';
            }
            if (! $order->isPending()) {
                return 'already-'.$order->status;
            }

            $user = User::whereKey($order->user_id)->lockForUpdate()->first();
            if ($order->plan === 'lifetime') {
                $user->forceFill(['pro_lifetime' => true])->save();
            } else {
                $from = $user->pro_until && $user->pro_until->isFuture() ? $user->pro_until : now();
                $user->forceFill(['pro_until' => $from->copy()->addDays($order->days)])->save();
            }

            $order->update(['status' => 'paid', 'paid_at' => now(), 'wayl_code' => $waylCode ?? $order->wayl_code]);

            return 'paid';
        });
    }

    /** Refund through Wayl and take back the matching share of Pro days. */
    public function refund(Order $order, int $amountIqd, string $reason, ?User $admin = null): Refund
    {
        $len = mb_strlen($reason);
        if ($len < 100 || $len > 1500) {
            throw new InvalidArgumentException('Refund reason must be 100–1500 characters.');
        }
        if ($order->status !== 'paid' || $amountIqd < 1 || $amountIqd > $order->amount_iqd - $order->refunded_iqd) {
            throw new InvalidArgumentException('Invalid refund amount.');
        }

        $response = $this->wayl->refund($order->id, $amountIqd, $reason);

        return DB::transaction(function () use ($order, $amountIqd, $reason, $admin, $response) {
            $order = Order::whereKey($order->id)->lockForUpdate()->first();
            $days = (int) round($order->days * $amountIqd / $order->amount_iqd);

            $user = User::whereKey($order->user_id)->lockForUpdate()->first();
            if ($order->plan === 'lifetime') {
                // A full refund ends lifetime access; a partial one keeps it.
                if ($order->refunded_iqd + $amountIqd >= $order->amount_iqd) {
                    $user->forceFill(['pro_lifetime' => false])->save();
                }
            } elseif ($user->pro_until) {
                $user->forceFill(['pro_until' => $user->pro_until->copy()->subDays($days)])->save();
            }

            $order->refunded_iqd += $amountIqd;
            if ($order->refunded_iqd >= $order->amount_iqd) {
                $order->status = 'refunded';
            }
            $order->save();

            return $order->refunds()->create([
                'admin_id' => $admin?->id,
                'amount_iqd' => $amountIqd,
                'reason' => $reason,
                'days_removed' => $days,
                'response' => $response,
            ]);
        });
    }
}
