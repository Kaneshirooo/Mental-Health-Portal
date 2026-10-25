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
        
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'course' => 'required|string|max:255',
            'semester' => 'required|string|max:100',
            'department' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',

            // In case of emergency
            'guardian_name' => 'nullable|string|max:255',
            'guardian_relationship' => 'nullable|string|max:100',
            'guardian_contact' => 'nullable|string|max:30',
            'guardian_email' => 'nullable|email|max:255',
        ], [
            'profile_picture.image' => 'Please upload an image file (JPG, PNG or WebP).',
            'profile_picture.mimes' => 'Please upload a JPG, PNG or WebP image.',
            'profile_picture.max' => 'That photo is too large. Please use one under 5MB.',
            'guardian_email.email' => 'Please enter a valid emergency email address.',
        ]);

        $data = [
            'full_name' => $validated['full_name'],
            'contact_number' => $validated['contact_number'],
            'course' => $validated['course'],
            'semester' => $validated['semester'],
            'department' => $validated['department'],

            'guardian_name' => $validated['guardian_name'],
            'guardian_relationship' => $validated['guardian_relationship'],
            'guardian_contact' => $validated['guardian_contact'],
            'guardian_email' => $validated['guardian_email'],
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
