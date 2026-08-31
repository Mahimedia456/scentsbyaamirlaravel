<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $coupons = Coupon::query()
            ->withCount('usages')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($inner) => $inner
                ->where('code','like','%'.$request->q.'%')
                ->orWhere('name','like','%'.$request->q.'%')))
            ->when($request->filled('state'), function ($q) use ($request) {
                match ($request->state) {
                    'enabled' => $q->where('is_active', true),
                    'disabled' => $q->where('is_active', false),
                    'scheduled' => $q->whereNotNull('starts_at')->where('starts_at', '>', now()),
                    'expired' => $q->whereNotNull('ends_at')->where('ends_at', '<', now()),
                    default => null,
                };
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $summary = [
            'all' => Coupon::count(),
            'enabled' => Coupon::where('is_active', true)->count(),
            'scheduled' => Coupon::whereNotNull('starts_at')->where('starts_at', '>', now())->count(),
            'expired' => Coupon::whereNotNull('ends_at')->where('ends_at', '<', now())->count(),
        ];

        return view('admin.coupons.index', compact('coupons','summary'));
    }

    public function create()
    {
        return view('admin.coupons.form', ['coupon' => new Coupon]);
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.form', compact('coupon'));
    }

    public function store(Request $request)
    {
        Coupon::create($this->validated($request));

        return redirect()->route('admin.coupons.index')->with('success','Promotion created.');
    }

    public function update(Request $request, Coupon $coupon)
    {
        $coupon->update($this->validated($request, $coupon->id));

        return redirect()->route('admin.coupons.index')->with('success','Promotion updated.');
    }

    public function toggle(Coupon $coupon)
    {
        $coupon->update(['is_active' => !$coupon->is_active]);

        return back()->with('success', $coupon->is_active ? 'Promotion enabled.' : 'Promotion disabled.');
    }

    public function duplicate(Coupon $coupon)
    {
        $copy = $coupon->replicate();
        $copy->code = strtoupper($coupon->code . '-' . substr(str()->random(4), 0, 4));
        $copy->name = trim(($coupon->name ?: $coupon->code) . ' Copy');
        $copy->is_active = false;
        $copy->used_count = 0;
        $copy->save();

        return redirect()->route('admin.coupons.edit', $copy)->with('success','Promotion duplicated as disabled.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return back()->with('success','Promotion deleted.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        $data = $request->validate([
            'code' => ['required','string','max:80',Rule::unique('coupons','code')->ignore($id)],
            'name' => ['nullable','string','max:160'],
            'type' => ['required',Rule::in(['percentage','fixed'])],
            'value' => ['required','numeric','min:0'],
            'minimum_order' => ['nullable','numeric','min:0'],
            'maximum_discount' => ['nullable','numeric','min:0'],
            'usage_limit' => ['nullable','integer','min:1'],
            'usage_limit_per_customer' => ['nullable','integer','min:1'],
            'starts_at' => ['nullable','date'],
            'ends_at' => ['nullable','date','after_or_equal:starts_at'],
        ]);

        if ($data['type'] === 'percentage' && (float) $data['value'] > 100) {
            abort(422, 'Percentage discount cannot exceed 100%.');
        }

        $data['code'] = strtoupper(trim($data['code']));
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
