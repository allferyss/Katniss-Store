<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $role = in_array($request->query('role'), array_column(Role::cases(), 'value'), true) ? $request->query('role') : null;

        $users = User::withCount('orders')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
            ->when($role, fn ($q, $r) => $q->where('role', $r))
            ->latest()
            ->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'role'));
    }

    public function update(Request $request, User $user, ActivityLogger $logger)
    {
        $data = $request->validate(['role' => ['required', Rule::enum(Role::class)]]);
        $newRole = Role::from($data['role']);

        if ($user->id === $request->user()->id) {
            throw ValidationException::withMessages(['role' => 'Anda tidak dapat mengubah role akun sendiri.']);
        }

        if ($user->isAdmin() && $newRole !== Role::Admin && User::where('role', Role::Admin->value)->count() <= 1) {
            throw ValidationException::withMessages(['role' => 'Harus ada minimal satu admin.']);
        }

        $old = $user->role;
        $user->forceFill(['role' => $newRole])->save();

        $logger->log('user.role_changed', "Mengubah role {$user->email}: {$old->value} -> {$newRole->value}", $user);

        return back()->with('success', "Role {$user->name} diperbarui.");
    }
}
