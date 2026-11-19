<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Credits;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CreditsController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return $this->respond($request, fn (Credits $c) => $c->status());
    }

    public function consume(Request $request): JsonResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:'.implode(',', Credits::ACTIONS)],
            'text_hash' => ['nullable', 'string', 'regex:/^[0-9a-f]{64}$/'],
        ]);

        return $this->respond($request, fn (Credits $c) => $c->consume($data['action'], $data['text_hash'] ?? null));
    }

    /** Ensure the visitor cookie exists, then run the callback with it in place. */
    private function respond(Request $request, callable $fn): JsonResponse
    {
        $new = null;
        if (! $request->user() && ! $request->cookie(Credits::COOKIE)) {
            $new = Str::random(32);
            $request->cookies->set(Credits::COOKIE, $new);
        }

        $response = response()->json($fn(new Credits($request)))
            ->header('Cache-Control', 'no-store');

        if ($new) {
            $response->withCookie(Cookie::make(Credits::COOKIE, $new, 60 * 24 * 365, sameSite: 'lax'));
        }

        return $response;
    }
}
