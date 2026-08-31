<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $customer = Customer::create($data + ['is_active' => true]);

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->intended(route('account'))->with('success', 'Welcome to your Scents by Aamir account.');
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
