<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show the profile edit form for Counselor & Head Counselor.
     */
    public function edit()
    {
        $user = Auth::user();
        return view('counselor.profile', compact('user'));
    }

    /**
     * Update basic profile details (Name, Contact Number, Department/Specialization, Photo).
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'full_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = [
            'full_name' => trim($request->full_name),
            'contact_number' => $request->contact_number ? trim($request->contact_number) : null,
            'department' => $request->department ? trim($request->department) : null,
        ];

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $path = $request->file('profile_picture')->store('profiles', 'public');
            $data['profile_picture'] = $path;
        }

        $user->update($data);

        return back()->with('success', 'Profile information updated successfully.');
    }

    /**
     * Update security password.
     */
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

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password you entered is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    /**
     * Update login email address.
     */
    public function updateEmail(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'new_email' => 'required|string|email|max:255|unique:users,email,' . $user->user_id . ',user_id',
            'confirm_password_email' => 'required|string',
        ], [
            'new_email.unique' => 'This email address is already in use by another account.',
            'confirm_password_email.required' => 'Please enter your current password to confirm email change.',
        ]);

        if (!Hash::check($request->confirm_password_email, $user->password)) {
            return back()->withErrors(['confirm_password_email' => 'Password verification failed. Incorrect password.']);
        }

        $user->update([
            'email' => strtolower(trim($request->new_email))
        ]);

        return back()->with('success', 'Email address updated successfully.');
    }
}
