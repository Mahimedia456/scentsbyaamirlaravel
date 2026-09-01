<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Notifications\CustomerActivationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerActivationController extends Controller
{
    public function activate(Request $request, Customer $customer): RedirectResponse
    {
        abort_unless($request->hasValidSignature(), 403, 'This activation link is invalid or has expired.');

        if (!$customer->email_verified_at) {
            $customer->forceFill([
                'email_verified_at' => now(),
                'is_active' => true,
            ])->save();
        }

        return redirect()
            ->route('customer.login')
            ->with('success', 'Your email is verified and your account is active. You can now sign in.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:190'],
        ]);

        $customer = Customer::where('email', strtolower(trim($data['email'])))->first();

        // Do not leak whether an email exists.
        if ($customer && !$customer->email_verified_at) {
            try {
                $customer->notify(new CustomerActivationNotification());
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return back()->with('success', 'If that account is awaiting activation, a new activation email has been sent.');
    }
}
