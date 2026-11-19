<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Credits: one balance that does not renew (no daily reset). The server is the
 * source of truth; the browser asks before running an operation. Visitors are
 * identified by an HMAC of their IP and of a first-party cookie ID (whichever
 * has consumed more counts). Raw IPs are never stored. The `day` column only
 * records when a credit was used (Asia/Baghdad), for the admin statistics.
 */
class Credits
{
    public const ACTIONS = ['fix', 'app', 'preview', 'png', 'svg', 'batch'];

    /** Actions charged once per distinct text. Copying for an app («app») is charged on every click. */
    private const DEDUPED = ['fix', 'preview'];

    /** Actions only Pro users may run. */
    private const PRO_ONLY = ['svg', 'batch'];

    public const COOKIE = 'yv';

    public function __construct(private readonly Request $request) {}

    public static function today(): string
    {
        return Carbon::now(config('yameen.credits_timezone'))->toDateString();
    }

    public function user(): ?User
    {
        return $this->request->user();
    }

    public function limit(): ?int
    {
        $user = $this->user();
        if ($user?->isPro()) {
            return null; // unlimited
        }

        return (int) Setting::get($user ? 'credits_free' : 'credits_visitor');
    }

    public function cost(string $action): int
    {
        return match ($action) {
            'fix' => (int) Setting::get('cost_fix'),
            'app' => (int) Setting::get('cost_app'),
            'preview' => (int) Setting::get('cost_preview'),
            'png' => (int) Setting::get('cost_png'),
            default => 0,
        };
    }

    public function used(): int
    {
        $query = fn () => DB::table('credit_usages');

        if ($user = $this->user()) {
            return (int) $query()->where('user_id', $user->id)->sum('cost');
        }

        $byIp = (int) $query()->whereNull('user_id')->where('ip_key', $this->ipKey())->sum('cost');
        $cookie = $this->cookieKey();
        $byCookie = $cookie ? (int) $query()->whereNull('user_id')->where('cookie_key', $cookie)->sum('cost') : 0;

        return max($byIp, $byCookie);
    }

    /** @return array{ok: bool, remaining: ?int, limit: ?int, pro: bool, reason?: string} */
    public function status(bool $ok = true, ?string $reason = null): array
    {
        $limit = $this->limit();
        $out = [
            'ok' => $ok,
            'remaining' => $limit === null ? null : max(0, $limit - $this->used()),
            'limit' => $limit,
            'pro' => $limit === null,
            'signed_in' => $this->user() !== null,
        ];
        if ($reason) {
            $out['reason'] = $reason;
        }

        return $out;
    }

    public function consume(string $action, ?string $textHash = null): array
    {
        $user = $this->user();
        $pro = $user?->isPro() ?? false;

        if (in_array($action, self::PRO_ONLY, true)) {
            return $this->status($pro, $pro ? null : 'pro');
        }
        if ($pro) {
            return $this->status();
        }

        $day = self::today();
        if ($textHash && in_array($action, self::DEDUPED, true) && $this->alreadyCharged($action, $textHash)) {
            return $this->status();
        }

        $cost = $this->cost($action);
        if ($this->used() + $cost > $this->limit()) {
            return $this->status(false, 'limit');
        }

        DB::table('credit_usages')->insert([
            'user_id' => $user?->id,
            'ip_key' => $user ? null : $this->ipKey(),
            'cookie_key' => $user ? null : $this->cookieKey(),
            'day' => $day,
            'action' => $action,
            'text_hash' => $textHash,
            'cost' => $cost,
            'created_at' => now(),
        ]);

        return $this->status();
    }

    private function alreadyCharged(string $action, string $textHash): bool
    {
        $q = DB::table('credit_usages')->where('action', $action)->where('text_hash', $textHash);
        if ($user = $this->user()) {
            return $q->where('user_id', $user->id)->exists();
        }

        return $q->whereNull('user_id')->where(function ($w) {
            $w->where('ip_key', $this->ipKey());
            if ($cookie = $this->cookieKey()) {
                $w->orWhere('cookie_key', $cookie);
            }
        })->exists();
    }

    public function ipKey(): string
    {
        return self::hmac('ip:'.$this->request->ip());
    }

    public function cookieKey(): ?string
    {
        $id = $this->request->cookie(self::COOKIE);

        return is_string($id) && $id !== '' ? self::hmac('cookie:'.$id) : null;
    }

    public static function hmac(string $value): string
    {
        return hash_hmac('sha256', $value, (string) config('app.key'));
    }
}
