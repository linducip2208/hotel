<?php

namespace App\Http\Controllers\Panel\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('property_id', app('current_property')->id)->with('roles')->paginate(50);
        $roles = Role::all();
        return view('panel.settings.users', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:10',
            'role' => 'required|string',
        ]);
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'property_id' => app('current_property')->id,
        ]);
        $user->assignRole($data['role']);
        return back();
    }

    public function update(Request $request, int $id)
    {
        $user = User::where('property_id', app('current_property')->id)->findOrFail($id);
        $user->update($request->only(['name', 'phone', 'is_active']));
        if ($role = $request->input('role')) $user->syncRoles([$role]);
        return back();
    }
}
