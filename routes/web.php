<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use App\Http\Controllers\Counselor\DashboardController as CounselorDashboard;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Student\AiChatController;
use App\Http\Controllers\Student\EmergencyCallController as StudentEmergencyCall;
use App\Http\Controllers\Counselor\EmergencyCallController as CounselorEmergencyCall;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Auth Routes
Route::get('/login-as/{id}', function ($id) {
    \Illuminate\Support\Facades\Auth::loginUsingId($id);
    return "Logged in as user ID " . $id;
});
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/register', [\App\Http\Controllers\Auth\RegistrationController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [\App\Http\Controllers\Auth\RegistrationController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// OTP Verification
Route::get('/verify-otp', [\App\Http\Controllers\Auth\OtpController::class, 'showVerifyForm'])->name('verify.otp');
Route::post('/verify-otp', [\App\Http\Controllers\Auth\OtpController::class, 'verify']);
Route::get('/resend-otp', [\App\Http\Controllers\Auth\OtpController::class, 'resend'])->name('resend.otp');
Route::post('/send-otp-background', [\App\Http\Controllers\Auth\OtpController::class, 'sendBackground'])->name('otp.send.background');

// Google OAuth
Route::get('/auth/google', [LoginController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [LoginController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {

    // Shared routes (all roles)
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count', [\App\Http\Controllers\NotificationController::class, 'count'])->name('notifications.count');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications/clear', [\App\Http\Controllers\NotificationController::class, 'clearAll'])->name('notifications.clear');
    Route::get('/resources', function() { return view('resources.index'); })->name('resources.index');
    Route::get('/emergency', function() { return view('emergency'); })->name('emergency');
    Route::post('/video-call/{call}/terminate', [\App\Http\Controllers\VideoCallController::class, 'terminate'])->name('video.call.terminate');
    Route::post('/video-call/{call}/message', [\App\Http\Controllers\VideoCallController::class, 'sendMessage'])->name('video.call.message');
    Route::get('/video-call/{call}/messages', [\App\Http\Controllers\VideoCallController::class, 'getMessages'])->name('video.call.messages');

    // Student Routes
    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [StudentDashboard::class, 'index'])->name('dashboard');
        Route::get('/reports', [\App\Http\Controllers\Student\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/{score}', [\App\Http\Controllers\Student\ReportController::class, 'show'])->name('reports.show');
        Route::get('/reports/session/{pre_id}', [\App\Http\Controllers\Student\ReportController::class, 'showSession'])->name('reports.session.show');
        Route::get('/chat', [AiChatController::class, 'index'])->name('chat');
        Route::post('/chat/new', [AiChatController::class, 'startConversation'])->name('chat.new');
        Route::post('/chat/send', [AiChatController::class, 'sendMessage'])->name('chat.send');
        Route::post('/chat/pre-assessment', [AiChatController::class, 'generatePreAssessment'])->name('chat.pre-assessment');
        
        Route::get('/assessment', [\App\Http\Controllers\Student\AssessmentController::class, 'index'])->name('assessment');
        Route::post('/assessment', [\App\Http\Controllers\Student\AssessmentController::class, 'store'])->name('assessment.store');
        Route::get('/assessment/results/{score}', [\App\Http\Controllers\Student\AssessmentController::class, 'results'])->name('assessment.results');
        Route::post('/assessment/translate', [\App\Http\Controllers\Student\AssessmentController::class, 'translate'])->name('assessment.translate');
        Route::get('/notes', [\App\Http\Controllers\Student\AnonymousNoteController::class, 'index'])->name('notes.index');
        Route::post('/notes', [\App\Http\Controllers\Student\AnonymousNoteController::class, 'store'])->name('notes.store');
        Route::post('/notes/{note}/reply', [\App\Http\Controllers\Student\AnonymousNoteController::class, 'reply'])->name('notes.reply');
        
        Route::get('/mindfulness', [\App\Http\Controllers\Student\MindfulnessController::class, 'index'])->name('mindfulness.index');
        Route::post('/mindfulness/ai-session', [\App\Http\Controllers\Student\MindfulnessController::class, 'generateAiSession'])->name('mindfulness.ai-session');
        Route::get('/mindfulness/recommendation', [\App\Http\Controllers\Student\MindfulnessController::class, 'getRecommendation'])->name('mindfulness.recommendation');
        Route::get('/mood', [\App\Http\Controllers\Student\MoodJournalController::class, 'index'])->name('mood');
        Route::post('/mood', [\App\Http\Controllers\Student\MoodJournalController::class, 'store'])->name('mood.store');
        Route::patch('/mood/{mood}', [\App\Http\Controllers\Student\MoodJournalController::class, 'update'])->name('mood.update');
        Route::get('/mood/insight', [\App\Http\Controllers\Student\MoodJournalController::class, 'insight'])->name('mood.insight');
        
        Route::get('/appointments', [\App\Http\Controllers\Student\AppointmentController::class, 'index'])->name('appointments');
        Route::post('/appointments/book', [\App\Http\Controllers\Student\AppointmentController::class, 'store'])->name('appointments.book');
        Route::post('/appointments/{appointment}/cancel', [\App\Http\Controllers\Student\AppointmentController::class, 'cancel'])->name('appointments.cancel');
        
        Route::get('/survey/{appointment}', [\App\Http\Controllers\Student\SurveyController::class, 'show'])->name('survey.show');
        Route::post('/survey/{appointment}', [\App\Http\Controllers\Student\SurveyController::class, 'store'])->name('survey.store');

        Route::get('/profile', [\App\Http\Controllers\Student\ProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/profile', [\App\Http\Controllers\Student\ProfileController::class, 'update'])->name('profile.update');

        // Emergency Call routes
        Route::post('/emergency-call/request', [StudentEmergencyCall::class, 'request'])->name('emergency.call.request');
        Route::get('/emergency-call/{call}/status', [StudentEmergencyCall::class, 'status'])->name('emergency.call.status');
        Route::post('/emergency-call/{call}/end', [StudentEmergencyCall::class, 'end'])->name('emergency.call.end');
        Route::post('/emergency-call/{call}/message', [StudentEmergencyCall::class, 'sendMessage'])->name('emergency.call.message');
        Route::get('/emergency-call/{call}/messages', [StudentEmergencyCall::class, 'getMessages'])->name('emergency.call.messages');

        // Dedicated Video Call Page
        Route::get('/video-call/{call}', [\App\Http\Controllers\VideoCallController::class, 'show'])->name('video.call');
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
        Route::put('/students/{student}', [\App\Http\Controllers\Counselor\StudentController::class, 'update'])->name('students.update');
        Route::delete('/students/{student}', [\App\Http\Controllers\Counselor\StudentController::class, 'destroy'])->name('students.destroy');
        Route::post('/students/{student}/note', [\App\Http\Controllers\Counselor\StudentController::class, 'addNote'])->name('students.note');
        Route::post('/students/{student}/ai-summary', [\App\Http\Controllers\Counselor\StudentController::class, 'aiSummary'])->name('students.ai-summary');
        Route::get('/students/{student}/session/{pre_id}', [\App\Http\Controllers\Counselor\StudentController::class, 'showSession'])->name('students.session.show');
        Route::get('/students/{student}/export', [\App\Http\Controllers\Counselor\StudentController::class, 'export'])->name('students.export');

        Route::get('/ledger', [\App\Http\Controllers\Counselor\LedgerController::class, 'index'])->name('ledger.index');
        Route::get('/ledger/export', [\App\Http\Controllers\Counselor\LedgerController::class, 'export'])->name('ledger.export');
        
        // Profile Management Routes
        Route::get('/profile', [\App\Http\Controllers\Counselor\ProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/profile', [\App\Http\Controllers\Counselor\ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/password', [\App\Http\Controllers\Counselor\ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::post('/profile/email', [\App\Http\Controllers\Counselor\ProfileController::class, 'updateEmail'])->name('profile.email');

        // AI Routes
        Route::post('/ai/suggest-reply', [CounselorDashboard::class, 'suggestReply'])->name('ai.suggest');

        // Clinical Safety Toggle
        Route::post('/emergency/toggle', [CounselorDashboard::class, 'toggleEmergencyStatus'])->name('emergency.toggle');


        // Emergency Call routes
        Route::get('/emergency-calls/logs', [CounselorEmergencyCall::class, 'logs'])->name('emergency.calls.logs');
        Route::get('/emergency-calls/pending', [CounselorEmergencyCall::class, 'pending'])->name('emergency.calls.pending');
        Route::post('/emergency-calls/{call}/accept', [CounselorEmergencyCall::class, 'accept'])->name('emergency.calls.accept');
        Route::post('/emergency-calls/{call}/decline', [CounselorEmergencyCall::class, 'decline'])->name('emergency.calls.decline');
        Route::post('/emergency-calls/{call}/end', [CounselorEmergencyCall::class, 'end'])->name('emergency.calls.end');
        Route::get('/emergency-calls/{call}/status', [CounselorEmergencyCall::class, 'status'])->name('emergency.calls.status');
        Route::post('/emergency-calls/{call}/message', [CounselorEmergencyCall::class, 'sendMessage'])->name('emergency.calls.message');
        Route::get('/emergency-calls/{call}/messages', [CounselorEmergencyCall::class, 'getMessages'])->name('emergency.calls.messages');
        Route::get('/emergency-calls/{call}/history', [CounselorEmergencyCall::class, 'history'])->name('emergency.calls.history');
        
        // Dedicated Video Call Page
        Route::get('/video-call/{call}', [\App\Http\Controllers\VideoCallController::class, 'show'])->name('video.call');
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
Route::get('/debug-log', function() { return response(file_exists(storage_path('logs/laravel.log')) ? substr(file_get_contents(storage_path('logs/laravel.log')), -5000) : 'No log file', 200, ['Content-Type' => 'text/plain']); });
