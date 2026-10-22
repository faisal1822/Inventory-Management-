<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $role = $request->query('role');
        $status = $request->query('status');

        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($role) {
            $query->where('role', $role);
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', (bool) $status);
        }

        $users = $query->latest('id')->paginate(10)->withQueryString();

        return view('users.index', compact('users', 'search', 'role', 'status'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('users.index')
            ->with('success', "User '{$user->name}' ({$user->role}) created successfully.");
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        // Prevent removing the last admin's role
        if ($user->isAdmin() && $request->role !== 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->withInput()->with('error', 'Cannot change the role of the only remaining administrator.');
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->phone = $request->phone;
        $user->is_active = $request->boolean('is_active', true);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('success', "User '{$user->name}' updated successfully.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account while logged in.');
        }

        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'Cannot delete the only remaining administrator.');
        }

        // Check if user has associated transactions
        if ($user->purchases()->count() > 0 || $user->sales()->count() > 0) {
            return back()->with('error', "Cannot delete user '{$user->name}' because they have recorded transactions. You can deactivate their account instead.");
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User account deleted successfully.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot deactivate your own logged-in account.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusStr = $user->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "User '{$user->name}' has been {$statusStr}.");
    }
}
