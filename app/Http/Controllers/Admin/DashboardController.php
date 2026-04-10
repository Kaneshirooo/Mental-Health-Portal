<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Appointment;
use App\Models\SessionLog;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'students_count' => User::where('user_type', 'student')->count(),
            'counselors_count' => User::where('user_type', 'counselor')->count(),
            'total_appointments' => Appointment::count(),
            'recent_logins' => SessionLog::with('user')
                ->orderBy('login_time', 'desc')
                ->limit(10)
                ->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
