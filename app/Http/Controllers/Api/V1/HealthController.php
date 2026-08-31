<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $database = 'connected';
        $driver = config('database.default');

        try {
            DB::select('select 1 as ok');
        } catch (Throwable) {
            $database = 'disconnected';
        }

        return response()->json([
            'success' => $database === 'connected',
            'data' => [
                'service' => 'Scents by Aamir API',
                'version' => '1.0.0',
                'database' => $database,
                'driver' => $driver,
                'environment' => app()->environment(),
                'timestamp' => now()->toIso8601String(),
            ],
        ], $database === 'connected' ? 200 : 503);
    }
}
