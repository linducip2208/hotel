<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index() { return view('admin.admin-users.index', ['users' => AdminUser::paginate(50)]); }
    public function create() { return view('admin.admin-users.create'); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|unique:admin_users,email',
            'password' => 'required|min:10',
            'name' => 'required|string',
            'role' => 'required|in:super_admin,sales,support,finance,dev_ops,read_only',
        ]);
        AdminUser::create($data + ['password' => Hash::make($data['password'])]);
        return redirect()->route('admin.admin-users.index');
    }

    public function show(int $id) { return view('admin.admin-users.show', ['user' => AdminUser::findOrFail($id)]); }
    public function edit(int $id) { return view('admin.admin-users.edit', ['user' => AdminUser::findOrFail($id)]); }

    public function update(Request $request, int $id)
    {
        $user = AdminUser::findOrFail($id);
        $user->update($request->only(['name', 'role', 'is_active']));
        return back();
    }

    public function destroy(int $id) { AdminUser::findOrFail($id)->delete(); return back(); }
}
