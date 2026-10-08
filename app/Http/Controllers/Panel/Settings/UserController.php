<?php

namespace App\Http\Controllers\Panel\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('property_id', app('current_property')->id)->with('roles')->paginate(50);
        $roles = Role::orderBy('name')->get();

        return view('panel.settings.users', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:10',
            'role' => 'required|string|exists:roles,name',
        ]);
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'property_id' => app('current_property')->id,
        ]);
        $user->assignRole($data['role']);

        app(AuditLogger::class)->record('user.created', $user, ['role' => $data['role']]);

        return back()->with('success', 'Akun staff berhasil dibuat.');
    }

    public function update(Request $request, int $id)
    {
        $user = User::where('property_id', app('current_property')->id)->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:30',
            'role' => 'required|string|exists:roles,name',
        ]);

        $before = ['name' => $user->name, 'phone' => $user->phone, 'roles' => $user->roles->pluck('name')->all()];

        $user->update(['name' => $data['name'], 'phone' => $data['phone'] ?? null]);

        if ($user->roles->pluck('name')->diff([$data['role']])->isNotEmpty() || $user->roles->count() === 0) {
            $user->syncRoles([$data['role']]);
        }

        app(AuditLogger::class)->record('user.updated', $user, ['role' => $data['role']], $before, [
            'name' => $user->name, 'phone' => $user->phone, 'roles' => [$data['role']],
        ]);

        return back()->with('success', 'Akun diperbarui.');
    }

    /** Deactivate/activate. Users with transaction history are never deleted — archived instead. */
    public function toggleActive(Request $request, int $id)
    {
        $user = User::where('property_id', app('current_property')->id)->findOrFail($id);

        if ($user->id === $request->user()->id) {
            return back()->withErrors(['users' => 'Tidak dapat menonaktifkan akun Anda sendiri.']);
        }

        $user->update(['is_active' => ! $user->is_active]);

        // Force logout by revoking active sessions + API tokens.
        if (! $user->is_active) {
            \DB::table('sessions')->where('user_id', $user->id)->delete();
            $user->tokens()->delete();
        }

        app(AuditLogger::class)->record('user.status_changed', $user, [
            'is_active' => $user->is_active,
        ]);

        return back()->with('success', $user->is_active ? 'Akun diaktifkan.' : 'Akun dinonaktifkan dan semua sesi dicabut.');
    }

    /** Reset password → generates a one-time temporary password shown once to the operator. */
    public function resetPassword(Request $request, int $id)
    {
        $user = User::where('property_id', app('current_property')->id)->findOrFail($id);

        $temp = 'Tmp-'.Str::random(10);
        $user->update(['password' => Hash::make($temp)]);

        \DB::table('sessions')->where('user_id', $user->id)->delete();
        $user->tokens()->delete();

        app(AuditLogger::class)->record('user.password_reset', $user, [
            'reset_by' => $request->user()->email,
        ]);

        session()->flash('temp_password', $temp);
        session()->flash('temp_password_user', $user->name);

        return back()->with('success', 'Password direset. Simpan password sementara di bawah ini — hanya ditampilkan sekali.');
    }
}
