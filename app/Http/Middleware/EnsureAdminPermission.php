<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        abort_unless(auth()->check() && auth()->user()?->canAdmin($permission), 403, 'You do not have permission to access this admin module.');

        return $next($request);
    }
}
