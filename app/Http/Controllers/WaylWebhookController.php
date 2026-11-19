<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWaylEvent;
use App\Models\WaylEvent;
use App\Services\Wayl\WaylClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WaylWebhookController extends Controller
{
    public function __invoke(Request $request, WaylClient $wayl): Response
    {
        // 1. Verify the signature over the raw body before anything else.
        $raw = $request->getContent();
        if (! $wayl->verifySignature($raw, $request->header('x-wayl-signature-256'))) {
            return response('', 401);
        }

        // 2. Store the raw event, answer immediately, process on the queue.
        $payload = json_decode($raw, true) ?: [];
        $event = WaylEvent::create([
            'source' => 'webhook',
            'reference_id' => WaylClient::referenceId($payload),
            'payload' => $raw,
        ]);
        ProcessWaylEvent::dispatch($event->id);

        return response('', 204);
    }
}
