<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Services\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminNotificationController extends Controller
{
    public function index(AdminNotificationService $service): View
    {
        $service->refreshSystemAlerts();

        $notifications = AdminNotification::query()
            ->visible()
            ->latest()
            ->paginate(30);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function read(AdminNotification $notification): RedirectResponse
    {
        if (!$notification->read_at) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        return back();
    }

    public function readAll(): RedirectResponse
    {
        AdminNotification::query()
            ->visible()
            ->unread()
            ->update(['read_at' => now(), 'updated_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function dismiss(AdminNotification $notification): RedirectResponse
    {
        $notification->forceFill([
            'dismissed_at' => now(),
            'read_at' => $notification->read_at ?: now(),
        ])->save();

        return back()->with('success', 'Notification dismissed.');
    }
}
