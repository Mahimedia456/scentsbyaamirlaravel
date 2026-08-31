<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminUserController extends Controller
{
    public function index()
    {
        $setupRequired = !Schema::hasTable('users');
        $users = $setupRequired ? collect() : User::latest()->get();
        return view('admin.admin-users.index', compact('users', 'setupRequired'));
    }

    public function store(Request $request)
    {
        if (!Schema::hasTable('users')) {
            return back()->with('error', 'Users table is not installed yet. Run php artisan migrate first.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:10',
            'role' => 'required|in:super_admin,admin,editor',
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = true;
        User::create($data);

        return back()->with('success', 'Admin user created.');
    }
}
