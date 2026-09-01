<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Notifications\CustomerActivationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CustomerAuthController extends Controller
{
    public function login(Request $request)
    {
        if ($request->filled('redirect')) {
            session()->put('url.intended', $request->string('redirect')->toString());
        }

        return view('store.auth.login');
    }

    public function authenticate(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);

        $customer = Customer::where('email', strtolower(trim($data['email'])))->first();

        if ($customer && Hash::check($data['password'], (string) $customer->password)) {
            if (!$customer->email_verified_at || !$customer->is_active) {
                return back()
                    ->withErrors(['email' => 'Please activate your account from the email we sent before signing in.'])
                    ->with('activation_email', $customer->email)
                    ->onlyInput('email');
            }
        }

        if (Auth::guard('customer')->attempt([
            'email' => $data['email'],
            'password' => $data['password'],
            'is_active' => 1,
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('account'));
        }

        return back()->withErrors(['email' => 'Email or password is incorrect.'])->onlyInput('email');
    }

    public function register()
    {
        return view('store.auth.register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required','string','max:100'],
            'last_name' => ['nullable','string','max:100'],
            'email' => ['required','email','max:190','unique:customers,email'],
            'phone' => ['nullable','string','max:40','unique:customers,phone'],
            'password' => ['required','confirmed',Password::min(8)],
        ]);

        $data['email'] = strtolower(trim($data['email']));

        $customer = Customer::create($data + [
            'is_active' => false,
            'email_verified_at' => null,
        ]);

        try {
            $customer->notify(new CustomerActivationNotification());
            $message = 'Account created. Check your email and click the activation link before signing in.';
        } catch (\Throwable $e) {
            report($e);
            $message = 'Account created, but the activation email could not be delivered. Use Resend activation after mail is configured.';
        }

        return redirect()
            ->route('customer.login')
            ->with('success', $message);
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
