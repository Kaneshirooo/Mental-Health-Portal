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
use Illuminate\Database\QueryException;
use Carbon\Carbon;

class RegistrationController extends Controller
{
    /**
     * Normalize a user supplied string before it is stored:
     * trim, drop control characters (newlines, NUL bytes, ...) and
     * collapse repeated whitespace.
     */
    protected function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';
        $value = preg_replace('/\s+/u', ' ', $value) ?? '';

        return trim($value);
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function showCounselorForm()
    {
        return view('auth.register_counselor');
    }

    public function registerCounselor(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'staff_id' => 'required|string|max:50|unique:users,roll_number',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'contact_number' => 'required|string|max:20',
            'department' => 'nullable|string|max:255',
        ], [
            'staff_id.unique' => 'This Staff / Faculty ID is already registered.',
            'email.unique' => 'This Email Address is already registered. Please sign in instead.',
            'password.min' => 'Password must be at least 6 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            'contact_number.required' => 'Please provide your contact number.',
            'full_name.max' => 'Your name must be 255 characters or fewer.',
            'staff_id.max' => 'Your Staff / Faculty ID must be 50 characters or fewer.',
            'email.max' => 'Your email address must be 255 characters or fewer.',
            'contact_number.max' => 'Your contact number must be 20 characters or fewer.',
            'department.max' => 'Your department must be 255 characters or fewer.',
        ]);

        try {
            DB::beginTransaction();

            $idProofPath = null;
            if ($request->hasFile('id_proof')) {
                $idProofPath = $request->file('id_proof')->store('id_proofs', 'public');
            }

            User::create([
                'full_name' => $this->clean($request->full_name),
                'roll_number' => $this->clean($request->staff_id),
                'email' => strtolower($this->clean($request->email)),
                'password' => Hash::make($request->password),
                'user_type' => UserRole::COUNSELOR->value,
                'contact_number' => $this->clean($request->contact_number),
                'department' => $this->clean($request->department),
                'id_proof_path' => $idProofPath,
                'verification_status' => 'verified',
            ]);

            DB::commit();

            return redirect()->route('login')->with('success', 'Counselor account created! You will be taken to the counselor dashboard when you log in.');
        } catch (QueryException $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Counselor Registration Database Error: ' . $e->getMessage(), ['exception' => $e]);

            return back()->withInput()->withErrors([
                'email' => 'We could not save your account because one of the details is too long or invalid. Please shorten the highlighted fields and try again.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Counselor Registration Error: ' . $e->getMessage());

            return back()->withInput()->withErrors(['email' => 'Registration failed. Please check your details and try again.']);
        }
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
            'full_name.max' => 'Your name must be 255 characters or fewer.',
            'student_id.max' => 'Your Student / Faculty ID must be 50 characters or fewer.',
            'email.max' => 'Your email address must be 255 characters or fewer.',
            'contact_number.max' => 'Your contact number must be 20 characters or fewer.',
            'department.max' => 'Your department must be 255 characters or fewer.',
            'course.max' => 'Your course must be 255 characters or fewer.',
            'semester.max' => 'Your semester must be 100 characters or fewer.',
            'guardian_name.max' => 'Your guardian name must be 255 characters or fewer.',
            'guardian_relationship.max' => 'Your guardian relationship must be 100 characters or fewer.',
            'guardian_contact.max' => 'Your guardian contact number must be 30 characters or fewer.',
            'guardian_email.max' => 'Your guardian email address must be 255 characters or fewer.',
        ]);


        try {
            DB::beginTransaction();

            $idProofPath = null;
            if ($request->hasFile('id_proof')) {
                $idProofPath = $request->file('id_proof')->store('id_proofs', 'public');
            }

            $user = User::create([
                'full_name' => $this->clean($request->full_name),
                'roll_number' => $this->clean($request->student_id),
                'email' => strtolower($this->clean($request->email)),
                'password' => Hash::make($request->password),
                'user_type' => UserRole::STUDENT->value,
                'contact_number' => $this->clean($request->contact_number),
                'date_of_birth' => $request->date_of_birth ? $request->date_of_birth : null,
                'gender' => $request->gender ? $request->gender : null,
                'department' => $this->clean($request->department),
                'course' => $this->clean($request->course),
                'semester' => $this->clean($request->semester),
                'id_proof_path' => $idProofPath,
                'verification_status' => 'pending',
                'guardian_name' => $this->clean($request->guardian_name),
                'guardian_relationship' => $this->clean($request->guardian_relationship),
                'guardian_contact' => $this->clean($request->guardian_contact),
                'guardian_email' => $request->guardian_email ? strtolower($this->clean($request->guardian_email)) : null,
            ]);

            DB::commit();

            return redirect()->route('login')->with('success', 'Your account has been created! Our team will verify your PSU ID shortly. Please log in with your credentials.');

        } catch (QueryException $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Registration Database Error: " . $e->getMessage(), [
                'exception' => $e
            ]);

            return back()->withInput()->withErrors([
                'email' => 'We could not save your account because one of the details is too long or invalid. Please shorten the highlighted fields and try again.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Registration Error: " . $e->getMessage(), [
                'exception' => $e
            ]);

            return back()->withInput()->withErrors(['email' => 'Registration failed. Please check your details and try again.']);
        }
    }
}



