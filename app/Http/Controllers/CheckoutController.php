<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use App\Services\Payments;
use App\Services\Wayl\WaylClient;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function pricing(): View
    {
        return view('pages.pricing', [
            'monthly' => Payments::plan('monthly'),
            'lifetime' => Payments::plan('lifetime'),
            'freeCredits' => Setting::get('credits_free'),
            'visitorCredits' => Setting::get('credits_visitor'),
        ]);
    }

    public function start(Request $request, Payments $payments, WaylClient $wayl): RedirectResponse
    {
        $plan = $request->validate(['plan' => ['required', 'in:monthly,lifetime']])['plan'];
        if (! $wayl->configured()) {
            return back()->withErrors(['plan' => __('ui.pricing.unavailable')]);
        }

        try {
            $order = $payments->checkout(
                $request->user(),
                $plan,
                __('ui.pricing.line_item', ['plan' => __('ui.pricing.'.$plan)]),
                route('webhooks.wayl'),
                fn (Order $o) => lroute('checkout.return', ['order' => $o->id]),
            );
        } catch (\Throwable $e) {
            Log::warning('Wayl checkout failed', ['error' => $e->getMessage()]);

            return back()->withErrors(['plan' => __('ui.pricing.unavailable')]);
        }

        return redirect()->away($order->checkout_url);
    }

    /** Wayl sends the buyer here. Never activates Pro by itself: it waits for the webhook. */
    public function returned(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        return view('pages.checkout-return', ['order' => $order]);
    }

    public function status(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->user_id === $request->user()?->id, 404);

        return response()->json([
            'status' => $order->status,
            'pro_until' => $request->user()->pro_until?->toIso8601String(),
        ])->header('Cache-Control', 'no-store');
    }
}
