<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index()
    {
        $setupRequired = !Schema::hasTable('users');
        $users = $setupRequired ? collect() : User::latest()->get();
        $roles = config('admin_roles.labels', []);

        return view('admin.admin-users.index', compact('users','setupRequired','roles'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $data = $this->validated($request);
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = true;
        $data['must_change_password'] = true;

        $user = User::create($data);

        $this->sendResetLink($user);

        return back()->with('success','Admin user created and password setup/reset email sent.');
    }

    public function update(Request $request, User $user)
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'email' => ['required','email','max:190',Rule::unique('users','email')->ignore($user->id)],
            'role' => ['required',Rule::in(array_keys(config('admin_roles.labels', [])))],
        ]);

        if ($user->id === auth()->id() && $data['role'] !== 'super_admin') {
            return back()->with('error','You cannot remove your own Super Admin role.');
        }

        $user->update($data);

        return back()->with('success','Admin user updated.');
    }

    public function toggle(User $user)
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        if ($user->id === auth()->id()) {
            return back()->with('error','You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);

        return back()->with('success',$user->is_active ? 'Admin account activated.' : 'Admin account deactivated.');
    }

    public function reset(User $user)
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $user->update(['must_change_password' => true]);
        $this->sendResetLink($user);

        return back()->with('success','Password reset link sent.');
    }

    public function destroy(User $user)
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        if ($user->id === auth()->id()) {
            return back()->with('error','You cannot delete your own account.');
        }

        if ($user->role === 'super_admin') {
            return back()->with('error','Change this user to a non-Super-Admin role before deletion.');
        }

        $user->delete();

        return back()->with('success','Admin user deleted.');
    }

    private function sendResetLink(User $user): void
    {
        $token = Password::broker()->createToken($user);
        $url = route('admin.password.reset', ['token'=>$token, 'email'=>$user->email]);

        Mail::send('emails.admin-password-reset', compact('user','url'), function ($mail) use ($user) {
            $mail->to($user->email, $user->name)->subject('Set your Scents by Aamir admin password');
        });
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required','string','max:120'],
            'email' => ['required','email','max:190','unique:users,email'],
            'password' => ['required','string','min:12'],
            'role' => ['required',Rule::in(array_keys(config('admin_roles.labels', [])))],
        ]);
    }
}
