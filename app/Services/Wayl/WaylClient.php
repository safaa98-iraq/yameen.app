<?php

namespace App\Services\Wayl;

use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Wayl payment links (https://api.thewayl.com). Everything Wayl-specific is
 * here so it can be adjusted if the official documentation differs.
 * The token and webhook secret come from .env and are never logged.
 */
class WaylClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $token,
        private readonly ?string $webhookSecret,
        private readonly string $env = 'test',
    ) {}

    public static function fromConfig(): self
    {
        $c = config('services.wayl');

        return new self($c['base_url'], $c['token'], $c['webhook_secret'], $c['env']);
    }

    public function configured(): bool
    {
        return filled($this->token) && filled($this->webhookSecret);
    }

    private function http(): PendingRequest
    {
        if (! $this->configured()) {
            throw new RuntimeException('Wayl is not configured.');
        }

        return Http::baseUrl($this->baseUrl)
            ->withHeaders(['X-WAYL-AUTHENTICATION' => $this->token])
            ->acceptJson()
            ->asJson()
            ->timeout(20);
    }

    /** Create a payment link. Returns ['url' => checkout URL, 'raw' => response JSON]. */
    public function createLink(Order $order, string $label, string $webhookUrl, string $redirectUrl): array
    {
        $response = $this->http()->post('/api/v1/links', [
            'env' => $this->env,
            'referenceId' => $order->id,
            'total' => $order->amount_iqd,
            'currency' => 'IQD',
            'customParameter' => '',
            'lineItem' => [[
                'label' => $label,
                'amount' => $order->amount_iqd, // line items must sum to total
                'type' => 'increase',
            ]],
            'webhookUrl' => $webhookUrl,
            'webhookSecret' => $this->webhookSecret,
            'redirectionUrl' => $redirectUrl,
        ]);

        if ($response->status() !== 201 || ! $response->json('data.url')) {
            throw new RuntimeException('Wayl link creation failed with HTTP '.$response->status());
        }

        return ['url' => $response->json('data.url'), 'raw' => $response->json()];
    }

    /** Current state of a link, for reconciliation. */
    public function getLink(string $referenceId): array
    {
        $response = $this->http()->get('/api/v1/links/'.rawurlencode($referenceId));
        if (! $response->successful()) {
            throw new RuntimeException('Wayl link lookup failed with HTTP '.$response->status());
        }

        return $response->json() ?? [];
    }

    /** Refund (part of) a payment. $reason must be 100–1500 characters. */
    public function refund(string $referenceId, int $amountIqd, string $reason): array
    {
        $response = $this->http()->post('/api/v1/refunds', [
            'referenceId' => $referenceId,
            'amount' => $amountIqd,
            'reason' => $reason,
        ]);
        if (! $response->successful()) {
            throw new RuntimeException('Wayl refund failed with HTTP '.$response->status());
        }

        return $response->json() ?? [];
    }

    /** HMAC-SHA256 of the raw body, compared in constant time. */
    public function verifySignature(string $rawBody, ?string $signature): bool
    {
        if (! filled($this->webhookSecret) || ! filled($signature)) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $rawBody, $this->webhookSecret), strtolower(trim($signature)));
    }

    /** Payment status from a webhook or link payload ("Paid", ...), wherever Wayl puts it. */
    public static function paymentStatus(array $payload): ?string
    {
        return $payload['paymentStatus'] ?? $payload['data']['paymentStatus'] ?? $payload['status'] ?? $payload['data']['status'] ?? null;
    }

    public static function referenceId(array $payload): ?string
    {
        return $payload['referenceId'] ?? $payload['data']['referenceId'] ?? null;
    }

    public static function code(array $payload): ?string
    {
        $code = $payload['code'] ?? $payload['data']['code'] ?? $payload['id'] ?? $payload['data']['id'] ?? null;

        return $code !== null ? (string) $code : null;
    }
}
