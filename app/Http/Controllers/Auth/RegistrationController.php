<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SessionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Enums\UserRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
            'student_id' => 'required|string|max:50|unique:users,roll_number',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
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
            'course' => 'nullable|string|max:255',
            'semester' => 'nullable|string|max:100',
        ], [
            'student_id.unique' => 'This Student ID / Faculty ID is already registered.',
            'email.unique' => 'This Email Address is already registered. Please sign in instead.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.regex' => 'Password must contain at least one uppercase letter and one special character.',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'full_name' => trim($request->full_name),
                'roll_number' => trim($request->student_id),
                'email' => strtolower(trim($request->email)),
                'password' => Hash::make($request->password),
                'user_type' => UserRole::STUDENT->value,
                'contact_number' => $request->contact_number ? trim($request->contact_number) : null,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'department' => $request->department,
                'course' => $request->course,
                'semester' => $request->semester,
            ]);

            try {
                SessionLog::create([
                    'user_id' => $user->user_id,
                    'login_time' => Carbon::now(),
                    'activity' => 'Registration Direct login',
                ]);
            } catch (\Exception $logEx) {
                \Illuminate\Support\Facades\Log::warning("Session log creation skipped: " . $logEx->getMessage());
            }

            DB::commit();

            Auth::login($user);
            return redirect()->route('student.dashboard')->with('success', 'Welcome to the Mental Health Portal! Your account has been successfully created.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Registration Error: " . $e->getMessage(), [
                'exception' => $e
            ]);
            
            $errorMessage = config('app.debug') 
                ? 'Registration failed: ' . $e->getMessage() 
                : 'An unexpected error occurred during registration. Please check your information and try again.';

            return back()->withInput()->withErrors(['email' => $errorMessage]);
        }
    }
}

