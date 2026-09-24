<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        if ($request->has('role_filter') && $request->role_filter != '') {
            $query->where('user_type', $request->role_filter);
        }

        $users = $query->latest()->get();

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'user_type' => 'required|in:student,counselor,admin',
        ]);

        $user = User::create([
            'full_name' => $request->full_name,
            'email'     => strtolower($request->email),
            'password'  => Hash::make($request->password),
            'user_type' => $request->user_type,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User created successfully!',
                'user'    => [
                    'user_id'    => $user->user_id,
                    'full_name'  => $user->full_name,
                    'email'      => $user->email,
                    'user_type'  => $user->user_type->value ?? (string) $user->user_type,
                    'created_at' => $user->created_at->format('F d, Y'),
                ],
            ]);
        }

        return back()->with('success', 'User created successfully!');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->user_id === auth()->id()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'You cannot delete your own account.'], 403);
            }
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'User deleted successfully!']);
        }

        return back()->with('success', 'User deleted successfully!');
    }
}
