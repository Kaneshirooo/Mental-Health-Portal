<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('student.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'full_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'course' => 'required|string|max:255',
            'semester' => 'required|string|max:100',
            'department' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = [
            'full_name' => $request->full_name,
            'contact_number' => $request->contact_number,
            'course' => $request->course,
            'semester' => $request->semester,
            'department' => $request->department,
        ];

        if ($request->hasFile('profile_picture')) {
            // Delete old picture if exists
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            
            $path = $request->file('profile_picture')->store('profiles', 'public');
            $data['profile_picture'] = $path;
        }

        $user->update($data);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'profile_picture_url' => $user->profile_picture ? asset('storage/' . $user->profile_picture) : null
            ]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Please enter your current password.',
            'new_password.min' => 'The new password must be at least 6 characters long.',
            'new_password.confirmed' => 'New password confirmation does not match.',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => 'The current password you entered is incorrect.']);
            }
            return back()->withErrors(['current_password' => 'The current password you entered is incorrect.']);
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->new_password)
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Password updated successfully.']);
        }

        return back()->with('success', 'Password updated successfully.');
    }
}
