<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use App\Http\Controllers\Counselor\DashboardController as CounselorDashboard;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Student\AiChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/register', [\App\Http\Controllers\Auth\RegistrationController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [\App\Http\Controllers\Auth\RegistrationController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// OTP Verification
Route::get('/verify-otp', [\App\Http\Controllers\Auth\OtpController::class, 'showVerifyForm'])->name('verify.otp');
Route::post('/verify-otp', [\App\Http\Controllers\Auth\OtpController::class, 'verify']);
Route::get('/resend-otp', [\App\Http\Controllers\Auth\OtpController::class, 'resend'])->name('resend.otp');

// Google OAuth
Route::get('/auth/google', [LoginController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [LoginController::class, 'handleGoogleCallback']);

// Authenticated Routes
Route::middleware(['auth'])->group(function () {

    // Shared routes (all roles)
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count', [\App\Http\Controllers\NotificationController::class, 'count'])->name('notifications.count');
    Route::post('/notifications/clear', [\App\Http\Controllers\NotificationController::class, 'clearAll'])->name('notifications.clear');
    Route::get('/resources', function() { return view('resources.index'); })->name('resources.index');
    Route::get('/emergency', function() { return view('emergency'); })->name('emergency');

    // Student Routes
    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [StudentDashboard::class, 'index'])->name('dashboard');
        Route::get('/reports', [\App\Http\Controllers\Student\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/{score}', [\App\Http\Controllers\Student\ReportController::class, 'show'])->name('reports.show');
        Route::get('/chat', [AiChatController::class, 'index'])->name('chat');
        Route::post('/chat/send', [AiChatController::class, 'sendMessage'])->name('chat.send');
        Route::post('/chat/pre-assessment', [AiChatController::class, 'generatePreAssessment'])->name('chat.pre-assessment');
        
        Route::get('/assessment', [\App\Http\Controllers\Student\AssessmentController::class, 'index'])->name('assessment');
        Route::post('/assessment', [\App\Http\Controllers\Student\AssessmentController::class, 'store'])->name('assessment.store');
        Route::get('/assessment/results/{score}', [\App\Http\Controllers\Student\AssessmentController::class, 'results'])->name('assessment.results');
                Route::get('/notes', [\App\Http\Controllers\Student\AnonymousNoteController::class, 'index'])->name('notes.index');
        Route::post('/notes', [\App\Http\Controllers\Student\AnonymousNoteController::class, 'store'])->name('notes.store');
        Route::post('/notes/{note}/reply', [\App\Http\Controllers\Student\AnonymousNoteController::class, 'reply'])->name('notes.reply');
        
        Route::get('/mindfulness', [\App\Http\Controllers\Student\MindfulnessController::class, 'index'])->name('mindfulness.index');
        Route::post('/mindfulness/ai-session', [\App\Http\Controllers\Student\MindfulnessController::class, 'generateAiSession'])->name('mindfulness.ai-session');
        Route::get('/mindfulness/recommendation', [\App\Http\Controllers\Student\MindfulnessController::class, 'getRecommendation'])->name('mindfulness.recommendation');
        Route::get('/mood', [\App\Http\Controllers\Student\MoodJournalController::class, 'index'])->name('mood');
        Route::post('/mood', [\App\Http\Controllers\Student\MoodJournalController::class, 'store'])->name('mood.store');
        Route::get('/mood/insight', [\App\Http\Controllers\Student\MoodJournalController::class, 'insight'])->name('mood.insight');
        
        Route::get('/appointments', [\App\Http\Controllers\Student\AppointmentController::class, 'index'])->name('appointments');
        Route::post('/appointments/book', [\App\Http\Controllers\Student\AppointmentController::class, 'store'])->name('appointments.book');
    });

    // Counselor Routes
    Route::prefix('counselor')->name('counselor.')->middleware('role:counselor,admin')->group(function () {
        Route::get('/dashboard', [CounselorDashboard::class, 'index'])->name('dashboard');
        Route::get('/availability', [\App\Http\Controllers\Counselor\AvailabilityController::class, 'index'])->name('availability');
        Route::post('/availability', [\App\Http\Controllers\Counselor\AvailabilityController::class, 'store']);
        
        Route::get('/appointments', [\App\Http\Controllers\Counselor\AppointmentController::class, 'index'])->name('appointments.index');
        Route::post('/appointments/emergency', [\App\Http\Controllers\Counselor\AppointmentController::class, 'triggerEmergency'])->name('appointments.emergency');
        Route::post('/appointments/action', [\App\Http\Controllers\Counselor\AppointmentController::class, 'handleAction'])->name('appointments.action');
        Route::get('/notes', [\App\Http\Controllers\Counselor\AnonymousNoteController::class, 'index'])->name('notes.index');
        Route::post('/notes/{note}/reply', [\App\Http\Controllers\Counselor\AnonymousNoteController::class, 'reply'])->name('notes.reply');
        Route::post('/notes/{note}/status', [\App\Http\Controllers\Counselor\AnonymousNoteController::class, 'updateStatus'])->name('notes.status');

        Route::get('/students', [\App\Http\Controllers\Counselor\StudentController::class, 'index'])->name('students.index');
        Route::get('/students/{student}', [\App\Http\Controllers\Counselor\StudentController::class, 'show'])->name('students.show');
        Route::post('/students/{student}/note', [\App\Http\Controllers\Counselor\StudentController::class, 'addNote'])->name('students.note');
        Route::post('/students/{student}/ai-summary', [\App\Http\Controllers\Counselor\StudentController::class, 'aiSummary'])->name('students.ai-summary');
        Route::get('/students/{student}/export', [\App\Http\Controllers\Counselor\StudentController::class, 'export'])->name('students.export');

        Route::get('/ledger', [\App\Http\Controllers\Counselor\LedgerController::class, 'index'])->name('ledger.index');
        Route::get('/ledger/export', [\App\Http\Controllers\Counselor\LedgerController::class, 'export'])->name('ledger.export');
        
        // AI Routes
        Route::post('/ai/suggest-reply', [CounselorDashboard::class, 'suggestReply'])->name('ai.suggest');
    });

    // Admin Routes
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
        
        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::post('/users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
        Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
        
        Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [\App\Http\Controllers\Admin\ReportController::class, 'export'])->name('reports.export');

        Route::get('/staff', [\App\Http\Controllers\Admin\StaffController::class, 'index'])->name('staff.index');
        Route::post('/staff', [\App\Http\Controllers\Admin\StaffController::class, 'store'])->name('staff.store');
        Route::delete('/staff/{staff}', [\App\Http\Controllers\Admin\StaffController::class, 'destroy'])->name('staff.destroy');
    });
});
