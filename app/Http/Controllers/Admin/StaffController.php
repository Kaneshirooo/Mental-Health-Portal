<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $counselors = User::where('user_type', 'counselor')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('full_name', 'like', "%$search%")
                       ->orWhere('email', 'like', "%$search%");
                });
            })
            ->selectRaw('users.*, (SELECT COUNT(*) FROM appointments WHERE appointments.counselor_id = users.user_id) as appointments_count')
            ->orderBy('full_name')
            ->get();

        return view('admin.staff.index', compact('counselors', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'department' => 'nullable|string|max:255',
        ]);

        User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => 'counselor',
            'department' => $request->department,
        ]);

        return back()->with('success', 'Counselor "' . $request->full_name . '" added successfully.');
    }

    public function destroy(User $staff)
    {
        if ($staff->user_type !== 'counselor') {
            return back()->with('error', 'Only counselor accounts can be removed from this page.');
        }

        if ($staff->user_id === auth()->id()) {
            return back()->with('error', 'You cannot remove your own account.');
        }

        $staff->delete();

        return back()->with('success', 'Counselor removed successfully.');
    }
}
