<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class UblPaymentService
{
    public function __construct(
        private readonly CustomerNotificationService $notifications,
        private readonly TransactionalMailService $mail,
    ) {}

    public function checkoutAttempt(Order $order): PaymentTransaction
    {
        $existing = $order->paymentTransactions()
            ->where('provider', 'ubl')
            ->whereIn('status', ['created', 'registered'])
            ->latest('attempt')
            ->first();

        return $existing ?: $this->newAttempt($order);
    }

    public function newAttempt(Order $order): PaymentTransaction
    {
        if ($order->payment_method !== 'ubl_card') {
            throw new RuntimeException('This order is not configured for UBL card payment.');
        }

        if ($order->payment_status === 'paid') {
            $paid = $order->paymentTransactions()->where('provider', 'ubl')->where('status', 'paid')->latest()->first();
            if ($paid) return $paid;
            throw new RuntimeException('This order has already been paid.');
        }

        return DB::transaction(function () use ($order) {
            $attempt = ((int) $order->paymentTransactions()->where('provider', 'ubl')->max('attempt')) + 1;

            return PaymentTransaction::create([
                'order_id' => $order->id,
                'provider' => 'ubl',
                'public_token' => (string) Str::uuid(),
                'attempt' => max(1, $attempt),
                'environment' => (string) config('ubl.mode', 'sandbox'),
                'status' => 'created',
                'gateway_order_id' => $this->gatewayOrderId($order),
                'amount' => (float) $order->grand_total,
                'currency' => (string) config('ubl.currency', $order->currency ?: 'PKR'),
            ]);
        });
    }

    public function register(PaymentTransaction $transaction): PaymentTransaction
    {
        $transaction->loadMissing('order');

        if ($transaction->status === 'paid') return $transaction;
        if ($transaction->status === 'registered' && $transaction->payment_portal_url && $transaction->gateway_transaction_id) {
            return $transaction;
        }
        if ($transaction->status !== 'created') {
            throw new RuntimeException('This UBL payment attempt cannot be registered again.');
        }

        $request = [
            'Currency' => $transaction->currency,
            'ReturnPath' => $this->returnUrl($transaction),
            'TransactionHint' => (string) config('ubl.transaction_hint', 'CPT:Y;VCC:Y;'),
            'OrderID' => $transaction->gateway_order_id,
            'Channel' => (string) config('ubl.channel', 'Web'),
            'Amount' => number_format((float) $transaction->amount, 2, '.', ''),
            'Customer' => (string) config('ubl.customer'),
            'OrderName' => mb_substr(trim((string) config('ubl.order_name', 'Scents by Aamir')), 0, 24),
            'UserName' => (string) config('ubl.username'),
            'Password' => (string) config('ubl.password'),
        ];

        if (filled(config('ubl.store'))) $request['Store'] = (string) config('ubl.store');
        if (filled(config('ubl.terminal'))) $request['Terminal'] = (string) config('ubl.terminal');

        $safe = $request;
        $safe['Password'] = '[REDACTED]';
        $transaction->update(['request_payload' => ['Registration' => $safe], 'last_error' => null]);

        try {
            $response = $this->http()->post((string) config('ubl.base_url'), ['Registration' => $request]);
            $json = $response->json();

            if (!$response->successful() || !is_array($json)) {
                throw new RuntimeException('UBL registration returned HTTP '.$response->status().'.');
            }

            $gateway = data_get($json, 'Transaction', []);
            $code = (string) data_get($gateway, 'ResponseCode', '');
            $portal = data_get($gateway, 'PaymentPortal') ?: data_get($gateway, 'PaymentPage');
            $gatewayTransactionId = (string) data_get($gateway, 'TransactionID', '');

            $transaction->update([
                'registration_response' => $json,
                'response_code' => $code ?: null,
                'response_class' => data_get($gateway, 'ResponseClass'),
                'response_description' => data_get($gateway, 'ResponseDescription'),
                'gateway_unique_id' => data_get($gateway, 'UniqueID'),
            ]);

            if ($code !== '0' || !$portal || !$gatewayTransactionId) {
                $message = (string) (data_get($gateway, 'ResponseDescription') ?: 'UBL registration failed.');
                $transaction->update(['status' => 'failed', 'last_error' => $message, 'failed_at' => now()]);
                throw new RuntimeException($message);
            }

            if (!str_starts_with(strtolower((string) $portal), 'https://')) {
                $transaction->update(['status' => 'failed', 'last_error' => 'UBL returned an invalid payment portal URL.', 'failed_at' => now()]);
                throw new RuntimeException('UBL returned an invalid payment portal URL.');
            }

            $transaction->update([
                'status' => 'registered',
                'gateway_transaction_id' => $gatewayTransactionId,
                'payment_portal_url' => $portal,
                'registered_at' => now(),
            ]);

            return $transaction->fresh('order');
        } catch (Throwable $e) {
            if ($transaction->status === 'created') {
                $transaction->update([
                    'status' => 'failed',
                    'last_error' => Str::limit($e->getMessage(), 3000),
                    'failed_at' => now(),
                ]);
            }
            throw $e;
        }
    }

    public function finalize(PaymentTransaction $transaction, string $callbackTransactionId): PaymentTransaction
    {
        $transaction->loadMissing('order');
        if ($transaction->status === 'paid') return $transaction;

        if (!$transaction->gateway_transaction_id || !hash_equals((string) $transaction->gateway_transaction_id, trim($callbackTransactionId))) {
            throw new RuntimeException('UBL callback transaction reference does not match this payment attempt.');
        }

        $request = [
            'TransactionID' => (string) $transaction->gateway_transaction_id,
            'Customer' => (string) config('ubl.customer'),
            'UserName' => (string) config('ubl.username'),
            'Password' => (string) config('ubl.password'),
        ];

        try {
            $response = $this->http()->post((string) config('ubl.base_url'), ['Finalization' => $request]);
            $json = $response->json();
            if (!$response->successful() || !is_array($json)) {
                throw new RuntimeException('UBL finalization returned HTTP '.$response->status().'.');
            }

            $gateway = data_get($json, 'Transaction', []);
            $code = (string) data_get($gateway, 'ResponseCode', '');
            $description = (string) (data_get($gateway, 'ResponseDescription') ?: 'UBL payment could not be finalized.');
            $returnedOrderId = (string) data_get($gateway, 'OrderID', '');
            $returnedAmount = data_get($gateway, 'Amount.Value');

            $transaction->update([
                'finalization_response' => $json,
                'finalized_at' => now(),
                'response_code' => $code ?: null,
                'response_class' => data_get($gateway, 'ResponseClass'),
                'response_description' => $description,
                'gateway_unique_id' => data_get($gateway, 'UniqueID') ?: $transaction->gateway_unique_id,
                'approval_code' => data_get($gateway, 'ApprovalCode'),
                'card_brand' => data_get($gateway, 'CardBrand'),
                'masked_card_number' => data_get($gateway, 'CardNumber'),
            ]);

            if ($code !== '0') {
                $this->markFailed($transaction, $description);
                return $transaction->fresh('order');
            }

            if ($returnedOrderId !== '' && !hash_equals((string) $transaction->gateway_order_id, $returnedOrderId)) {
                $this->markFailed($transaction, 'UBL finalization OrderID mismatch.');
                return $transaction->fresh('order');
            }

            if ($returnedAmount !== null && $returnedAmount !== '') {
                if (abs((float) $returnedAmount - (float) $transaction->amount) > 0.009) {
                    $this->markFailed($transaction, 'UBL finalization amount mismatch.');
                    return $transaction->fresh('order');
                }
            }

            $wasAlreadyPaid = $transaction->order->payment_status === 'paid';

            DB::transaction(function () use ($transaction) {
                $transaction->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'failed_at' => null,
                    'last_error' => null,
                ]);

                $transaction->order->update([
                    'payment_status' => 'paid',
                    'payment_verification_status' => 'verified',
                    'payment_verified_at' => now(),
                    'payment_verified_by' => null,
                    'payment_rejection_reason' => null,
                    'payment_reference' => $transaction->gateway_transaction_id,
                    'status' => $transaction->order->status === 'pending' ? 'confirmed' : $transaction->order->status,
                ]);
            });

            $freshOrder = $transaction->order->fresh('items');
            if (!$wasAlreadyPaid) {
                if ($freshOrder->customer) $this->notifications->orderPlaced($freshOrder);
                $this->mail->order($freshOrder, 'placed');
            }

            return $transaction->fresh('order');
        } catch (Throwable $e) {
            $transaction->update(['last_error' => Str::limit($e->getMessage(), 3000)]);
            throw $e;
        }
    }

    public function markFailed(PaymentTransaction $transaction, string $message): void
    {
        DB::transaction(function () use ($transaction, $message) {
            $transaction->update([
                'status' => 'failed',
                'failed_at' => now(),
                'last_error' => Str::limit($message, 3000),
            ]);

            if ($transaction->order && $transaction->order->payment_status !== 'paid') {
                $transaction->order->update([
                    'payment_status' => 'failed',
                    'payment_verification_status' => 'failed',
                    'payment_rejection_reason' => Str::limit($message, 1000),
                ]);
            }
        });
    }

    private function http(): PendingRequest
    {
        $request = Http::acceptJson()
            ->asJson()
            ->connectTimeout((int) config('ubl.connect_timeout', 12))
            ->timeout((int) config('ubl.timeout', 30))
            ->retry(1, 350, throw: false)
            ->withOptions(['verify' => (bool) config('ubl.verify_ssl', true)]);

        if (defined('CURLOPT_SSLVERSION') && defined('CURL_SSLVERSION_TLSv1_2')) {
            $request = $request->withOptions([
                'curl' => [CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2],
                'verify' => (bool) config('ubl.verify_ssl', true),
            ]);
        }

        return $request;
    }

    private function returnUrl(PaymentTransaction $transaction): string
    {
        $relative = route('payments.ubl.return', ['token' => $transaction->public_token], false);
        return rtrim((string) config('ubl.public_url'), '/').'/'.ltrim($relative, '/');
    }

    private function gatewayOrderId(Order $order): string
    {
        $clean = strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string) $order->order_number));
        if ($clean === '') $clean = 'SBA'.$order->id;
        return substr($clean, 0, 16);
    }
}
