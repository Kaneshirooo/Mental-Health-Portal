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
                'min:6',
                'confirmed',
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
            'password.min' => 'Password must be at least 6 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
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
                'date_of_birth' => $request->date_of_birth ? $request->date_of_birth : null,
                'gender' => $request->gender ? $request->gender : null,
                'department' => $request->department ? $request->department : null,
                'course' => $request->course ? $request->course : null,
                'semester' => $request->semester ? $request->semester : null,
            ]);

            DB::commit();

            return redirect()->route('login')->with('success', 'Your account has been successfully created! Please log in with your credentials.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Registration Error: " . $e->getMessage(), [
                'exception' => $e
            ]);
            
            return back()->withInput()->withErrors(['email' => 'Registration failed: ' . $e->getMessage()]);
        }
    }
}



