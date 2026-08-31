<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request; use Symfony\Component\HttpFoundation\Response;
class EnsureCustomer { public function handle(Request $request, Closure $next): Response { if (!auth('customer')->check()) return redirect()->route('customer.login')->with('error','Please sign in to continue.'); if (!auth('customer')->user()->is_active) { auth('customer')->logout(); return redirect()->route('customer.login')->with('error','This account is inactive.'); } return $next($request); } }
