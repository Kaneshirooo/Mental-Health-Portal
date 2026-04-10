<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'student_id' => 'required|string|max:50',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/',
                'regex:/[!@#$%^&*(),.?":{}|<>]/',
            ],
            'contact_number' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string',
            'department' => 'nullable|string|max:255',
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter and one special character.',
        ]);

        $user = User::create([
            'full_name' => $request->full_name,
            'roll_number' => $request->student_id,
            'email' => strtolower($request->email),
            'password' => Hash::make($request->password),
            'user_type' => 'student',
            'contact_number' => $request->contact_number,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'department' => $request->department,
        ]);

        Auth::login($user);

        return redirect()->route('student.dashboard')->with('success', 'Welcome to the Mental Health Portal!');
    }
}
