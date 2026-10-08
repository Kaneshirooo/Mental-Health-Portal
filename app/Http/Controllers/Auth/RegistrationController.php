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
            'id_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'guardian_name' => 'required|string|max:255',
            'guardian_relationship' => 'required|string|max:100',
            'guardian_contact' => 'required|string|max:30',
            'guardian_email' => 'nullable|email|max:255',
        ], [
            'student_id.unique' => 'This Student ID / Faculty ID is already registered.',
            'email.unique' => 'This Email Address is already registered. Please sign in instead.',
            'password.min' => 'Password must be at least 6 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            'id_proof.required' => 'Please upload a photo of your school ID or proof of enrollment at PSU.',
            'guardian_name.required' => 'Please provide the name of your parent or guardian.',
            'guardian_contact.required' => 'Please provide a contact number for your parent or guardian.',
        ]);


        try {
            DB::beginTransaction();

            $idProofPath = null;
            if ($request->hasFile('id_proof')) {
                $idProofPath = $request->file('id_proof')->store('id_proofs', 'public');
            }

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
                'id_proof_path' => $idProofPath,
                'verification_status' => 'pending',
                'guardian_name' => trim($request->guardian_name),
                'guardian_relationship' => trim($request->guardian_relationship),
                'guardian_contact' => trim($request->guardian_contact),
                'guardian_email' => $request->guardian_email ? strtolower(trim($request->guardian_email)) : null,
            ]);

            DB::commit();

            return redirect()->route('login')->with('success', 'Your account has been created! Our team will verify your PSU ID shortly. Please log in with your credentials.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Registration Error: " . $e->getMessage(), [
                'exception' => $e
            ]);
            
            return back()->withInput()->withErrors(['email' => 'Registration failed: ' . $e->getMessage()]);
        }
    }
}



