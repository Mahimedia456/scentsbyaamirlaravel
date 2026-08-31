<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Schema;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('admin.login');
        }

        if (! auth()->user()?->isAdmin()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->withErrors([
                'email' => 'This account is not authorized for the admin panel.',
            ]);
        }

        $response = $next($request);

        if (
            in_array($request->method(), ['POST','PUT','PATCH','DELETE'], true)
            && Schema::hasTable('audit_logs')
        ) {
            try {
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => (string) ($request->route()?->getName() ?: strtolower($request->method())),
                    'entity_type' => $this->entityType($request),
                    'entity_id' => $this->entityId($request),
                    'ip_address' => $request->ip(),
                    'meta' => [
                        'method' => $request->method(),
                        'path' => $request->path(),
                        'status' => $response->getStatusCode(),
                        'user_agent' => mb_substr((string) $request->userAgent(), 0, 300),
                    ],
                ]);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $response;
    }

    private function entityType(Request $request): ?string
    {
        foreach (['product','order','customer','coupon','category','collection','page','journal_post','navigation','media','user'] as $key) {
            if ($request->route($key)) return $key;
        }
        return null;
    }

    private function entityId(Request $request): ?int
    {
        foreach (['product','order','customer','coupon','category','collection','page','journal_post','navigation','media','user'] as $key) {
            $value = $request->route($key);
            if (is_object($value) && method_exists($value,'getKey')) return (int) $value->getKey();
            if (is_numeric($value)) return (int) $value;
        }
        return null;
    }
}
