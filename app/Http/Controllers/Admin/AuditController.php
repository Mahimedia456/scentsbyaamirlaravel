<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

class AuditController extends Controller
{
    public function index()
    {
        $setupRequired = !Schema::hasTable('audit_logs');
        $logs = $setupRequired
            ? new LengthAwarePaginator([], 0, 50, 1, ['path' => request()->url(), 'query' => request()->query()])
            : AuditLog::latest()->paginate(50);

        return view('admin.audit.index', compact('logs', 'setupRequired'));
    }
}
