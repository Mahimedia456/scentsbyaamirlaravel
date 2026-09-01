<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;
use Throwable;

class CustomerPasswordController extends Controller
{
    public function requestForm(): View
    {
        return view('store.auth.forgot-password');
    }

    public function email(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:190'],
        ]);

        try {
            $status = Password::broker('customers')->sendResetLink([
                'email' => strtolower(trim($data['email'])),
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['email' => 'We could not send the reset email right now. Please try again shortly.'])
                ->onlyInput('email');
        }

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', __($status));
        }

        // Do not disclose whether a customer record exists.
        return back()->with('success', 'If an account exists for that email, a password reset link has been sent.');
    }

    public function resetForm(Request $request, string $token): View
    {
        return view('store.auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email'),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ]);

        $status = Password::broker('customers')->reset(
            $data,
            function (Customer $customer, string $password): void {
                $customer->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('customer.login')->with('success', 'Your password has been reset. You can now sign in.')
            : back()->withErrors(['email' => __($status)])->withInput($request->only('email'));
    }
}
