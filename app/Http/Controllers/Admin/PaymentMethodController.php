<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PaymentMethodController extends Controller
{
    public function index()
    {
        foreach ([
            ['cod', 'Cash on Delivery', 1, 10],
            ['ubl_card', 'Debit / Credit Card (UBL)', 0, 15],
            ['bank_transfer', 'Bank Transfer', 0, 20],
        ] as [$code, $name, $enabled, $sort]) {
            PaymentMethod::firstOrCreate(
                ['code' => $code],
                ['name' => $name, 'enabled' => $enabled, 'test_mode' => $code === 'ubl_card', 'sort_order' => $sort]
            );
        }

        $transactions = Schema::hasTable('payment_transactions')
            ? PaymentTransaction::with('order')->where('provider', 'ubl')->latest()->limit(20)->get()
            : collect();

        return view('admin.payments.index', [
            'methods' => PaymentMethod::whereIn('code', ['cod', 'ubl_card', 'bank_transfer'])->orderBy('sort_order')->get(),
            'transactions' => $transactions,
            'ubl' => [
                'mode' => config('ubl.mode'),
                'base_url' => config('ubl.base_url'),
                'public_url' => config('ubl.public_url'),
                'customer' => config('ubl.customer'),
                'currency' => config('ubl.currency'),
            ],
        ]);
    }

    public function update(Request $request, PaymentMethod $payment)
    {
        abort_unless(in_array($payment->code, ['cod', 'ubl_card', 'bank_transfer'], true), 404);
        $config = $payment->config ?: [];

        if ($payment->code === 'bank_transfer') {
            $config = $request->validate([
                'bank_name' => 'nullable|string|max:160',
                'account_title' => 'nullable|string|max:160',
                'account_number' => 'nullable|string|max:120',
                'iban' => 'nullable|string|max:120',
                'instructions' => 'nullable|string|max:1000',
            ]);
        }

        if ($payment->code === 'ubl_card') {
            $validated = $request->validate(['customer_note' => 'nullable|string|max:500']);
            $config = ['customer_note' => $validated['customer_note'] ?: 'Secure Visa / Mastercard payment via UBL hosted checkout.'];
        }

        $payment->update([
            'enabled' => $request->boolean('enabled'),
            'test_mode' => $payment->code === 'ubl_card' ? config('ubl.mode') !== 'production' : false,
            'config' => $config,
        ]);

        return back()->with('success', 'Payment method updated.');
    }
}
