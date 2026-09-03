<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Services\UblPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class UblPaymentController extends Controller
{
    public function start(string $token, UblPaymentService $ubl)
    {
        $transaction = PaymentTransaction::with('order')->where('provider', 'ubl')->where('public_token', $token)->firstOrFail();
        $this->authorizeAttempt($transaction);

        if ($transaction->status === 'paid') {
            return view('store.payments.ubl-result', compact('transaction'));
        }

        try {
            $transaction = $ubl->register($transaction);
        } catch (Throwable $e) {
            report($e);
            $transaction->refresh()->load('order');
            return view('store.payments.ubl-result', compact('transaction'));
        }

        return view('store.payments.ubl-redirect', compact('transaction'));
    }

    public function returned(Request $request, string $token, UblPaymentService $ubl)
    {
        $transaction = PaymentTransaction::with('order')->where('provider', 'ubl')->where('public_token', $token)->firstOrFail();

        if ($transaction->status === 'paid') {
            session()->forget('checkout_token');
            return view('store.payments.ubl-result', compact('transaction'));
        }

        $transactionId = trim((string) (
            $request->input('TransactionID')
            ?? $request->input('transactionId')
            ?? $request->input('transaction_id')
            ?? ''
        ));

        if ($transactionId === '') {
            $ubl->markFailed($transaction, 'UBL returned without a TransactionID.');
            $transaction->refresh()->load('order');
            return view('store.payments.ubl-result', compact('transaction'));
        }

        try {
            $transaction = $ubl->finalize($transaction, $transactionId);
        } catch (Throwable $e) {
            Log::warning('UBL payment finalization exception', [
                'payment_transaction_id' => $transaction->id,
                'order_id' => $transaction->order_id,
                'message' => $e->getMessage(),
            ]);
            $transaction->refresh()->load('order');
        }

        if ($transaction->status === 'paid') {
            session()->forget('checkout_token');
        }

        return view('store.payments.ubl-result', compact('transaction'));
    }

    public function retry(string $token, UblPaymentService $ubl)
    {
        $old = PaymentTransaction::with('order')->where('provider', 'ubl')->where('public_token', $token)->firstOrFail();
        $this->authorizeAttempt($old);

        if ($old->order->payment_status === 'paid') {
            return redirect()->route('payments.ubl.start', ['token' => $old->public_token]);
        }

        $next = $ubl->newAttempt($old->order);
        return redirect()->route('payments.ubl.start', ['token' => $next->public_token]);
    }

    private function authorizeAttempt(PaymentTransaction $transaction): void
    {
        $customerId = auth('customer')->id();
        $guestOrderIds = array_map('intval', session('guest_order_ids', []));

        $allowed = $customerId
            ? (int) $transaction->order->customer_id === (int) $customerId
            : in_array((int) $transaction->order_id, $guestOrderIds, true);

        abort_unless($allowed, 404);
    }
}
