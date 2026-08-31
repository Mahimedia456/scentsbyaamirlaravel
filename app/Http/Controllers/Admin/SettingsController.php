<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.index', [
            'settings' => StoreSetting::pluck('value','key'),
            'mail' => [
                'mailer' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'username' => config('mail.mailers.smtp.username'),
                'from' => config('mail.from.address'),
                'order_notification' => config('commerce.order_notification_email'),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'store_name'=>['required','string','max:120'],
            'currency'=>['required','in:PKR'],
            'support_email'=>['required','email','max:190'],
            'support_phone'=>['nullable','string','max:40'],
            'order_prefix'=>['required','string','max:20'],
            'store_tagline'=>['nullable','string','max:190'],
            'instagram_url'=>['nullable','url','max:500'],
            'facebook_url'=>['nullable','url','max:500'],
            'tiktok_url'=>['nullable','url','max:500'],
            'gift_wrap_fee'=>['nullable','numeric','min:0'],
        ]);

        foreach ($data as $key => $value) {
            StoreSetting::updateOrCreate(
                ['key'=>$key],
                ['group'=>$this->groupFor($key),'value'=>$value]
            );
        }

        return back()->with('success','Store settings saved.');
    }

    private function groupFor(string $key): string
    {
        if (str_ends_with($key,'_url')) return 'social';
        if (in_array($key,['support_email','support_phone'],true)) return 'support';
        if (in_array($key,['gift_wrap_fee','currency','order_prefix'],true)) return 'commerce';
        return 'general';
    }
}
