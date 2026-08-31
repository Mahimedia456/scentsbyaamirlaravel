<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\CustomerAdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::query()
            ->withCount('orders')
            ->withSum('orders', 'grand_total')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim((string) $request->q);
                $query->where(function ($inner) use ($term) {
                    $inner->where('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                match ($request->status) {
                    'active' => $query->where('is_active', true)->whereNull('admin_archived_at'),
                    'inactive' => $query->where('is_active', false)->whereNull('admin_archived_at'),
                    'archived' => $query->whereNotNull('admin_archived_at'),
                    default => null,
                };
            })
            ->when($request->filled('verification'), function ($query) use ($request) {
                $request->verification === 'verified'
                    ? $query->whereNotNull('email_verified_at')
                    : $query->whereNull('email_verified_at');
            })
            ->when($request->filled('marketing'), fn ($q) => $q->where('marketing_opt_in', $request->marketing === 'yes'))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $summary = [
            'all' => Customer::count(),
            'active' => Customer::where('is_active', true)->whereNull('admin_archived_at')->count(),
            'inactive' => Customer::where('is_active', false)->whereNull('admin_archived_at')->count(),
            'unverified' => Customer::whereNull('email_verified_at')->whereNull('admin_archived_at')->count(),
            'archived' => Customer::whereNotNull('admin_archived_at')->count(),
        ];

        return view('admin.customers.index', compact('customers', 'summary'));
    }

    public function create()
    {
        return view('admin.customers.form', ['customer' => new Customer]);
    }

    public function store(Request $request, CustomerAdminService $accounts)
    {
        $customer = Customer::create($this->validated($request));

        if ($request->boolean('send_activation') && filled($customer->email) && !$customer->email_verified_at) {
            $accounts->sendActivation($customer);
        }

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer created.');
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'addresses' => fn ($q) => $q->orderByDesc('is_default')->latest(),
            'orders' => fn ($q) => $q->latest('placed_at')->latest()->limit(50),
        ]);

        $metrics = [
            'orders' => $customer->orders()->count(),
            'spent' => (float) $customer->orders()->whereNotIn('status', ['cancelled'])->sum('grand_total'),
            'last_order' => $customer->orders()->latest('placed_at')->first(),
        ];

        return view('admin.customers.show', compact('customer', 'metrics'));
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validated($request, $customer->id));

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer)
    {
        $customer->update([
            'is_active' => false,
            'admin_archived_at' => now(),
            'admin_archived_by' => auth()->id(),
        ]);

        return redirect()->route('admin.customers.index')->with('success', 'Customer archived. Historical orders were preserved.');
    }

    public function restore(Customer $customer)
    {
        $customer->update([
            'admin_archived_at' => null,
            'admin_archived_by' => null,
        ]);

        return back()->with('success', 'Customer restored from archive.');
    }

    public function activate(Customer $customer, CustomerAdminService $accounts)
    {
        $customer->update(['is_active' => true, 'admin_archived_at' => null, 'admin_archived_by' => null]);
        $accounts->sendAccountStatus($customer->fresh(), true);

        return back()->with('success', 'Customer activated.');
    }

    public function deactivate(Customer $customer, CustomerAdminService $accounts)
    {
        $customer->update(['is_active' => false]);
        $accounts->sendAccountStatus($customer->fresh(), false);

        return back()->with('success', 'Customer deactivated.');
    }

    public function resendActivation(Customer $customer, CustomerAdminService $accounts)
    {
        if ($customer->email_verified_at) {
            return back()->with('success', 'Customer email is already verified.');
        }

        if (!filled($customer->email)) {
            return back()->withErrors(['email' => 'This customer does not have an email address.']);
        }

        $sent = $accounts->sendActivation($customer);

        return $sent
            ? back()->with('success', 'Activation email sent.')
            : back()->withErrors(['email' => 'Activation email could not be sent. Check Mail Diagnostics.']);
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:customers,id'],
            'action' => ['required', Rule::in(['activate','deactivate','archive','marketing_on','marketing_off'])],
        ]);

        DB::transaction(function () use ($data) {
            $query = Customer::query()->whereIn('id', $data['ids']);

            match ($data['action']) {
                'activate' => $query->update(['is_active' => true, 'admin_archived_at' => null, 'admin_archived_by' => null]),
                'deactivate' => $query->update(['is_active' => false]),
                'archive' => $query->update(['is_active' => false, 'admin_archived_at' => now(), 'admin_archived_by' => auth()->id()]),
                'marketing_on' => $query->update(['marketing_opt_in' => true]),
                'marketing_off' => $query->update(['marketing_opt_in' => false]),
            };
        });

        return back()->with('success', count($data['ids']) . ' customer(s) updated.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        $data = $request->validate([
            'first_name' => ['required','string','max:100'],
            'last_name' => ['nullable','string','max:100'],
            'company' => ['nullable','string','max:160'],
            'email' => ['nullable','email','max:190',Rule::unique('customers','email')->ignore($id)],
            'phone' => ['nullable','string','max:40',Rule::unique('customers','phone')->ignore($id)],
            'notes' => ['nullable','string','max:3000'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['marketing_opt_in'] = $request->boolean('marketing_opt_in');

        return $data;
    }
}
