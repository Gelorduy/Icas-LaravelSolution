<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    public function index()
    {
        abort_unless(PermissionService::userHasPermission(auth()->user(), 'admin.users.manage'), 403);

        $users = User::orderBy('created_at', 'desc')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            ];
        });

        $availableRoles = array_keys(config('permissions.roles', []));

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'availableRoles' => $availableRoles,
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(PermissionService::userHasPermission(auth()->user(), 'admin.users.manage'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', Rule::in(array_keys(config('permissions.roles', [])))],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->back()->with('success', 'User created successfully');
    }

    public function update(Request $request, User $user)
    {
        abort_unless(PermissionService::userHasPermission(auth()->user(), 'admin.users.manage'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'string', Rule::in(array_keys(config('permissions.roles', [])))],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        if ($validated['password']) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        return redirect()->back()->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        abort_unless(PermissionService::userHasPermission(auth()->user(), 'admin.users.manage'), 403);

        if ($user->id === auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'You cannot delete your own account']);
        }

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully');
    }
}

