<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\SessionLog;
use App\Traits\GeneratesClinicalExport;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    use GeneratesClinicalExport;

    public function index(Request $request)
    {
        $query = SessionLog::with('user')
            ->orderBy('login_time', 'desc')
            ->limit(100);

        if ($search = $request->search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('roll_number', 'like', "%$search%");
            });
        }

        if ($role = $request->role) {
            $query->whereHas('user', function ($q) use ($role) {
                $q->where('user_type', $role);
            });
        }

        $logs = $query->get();

        return view('counselor.ledger', compact('logs'));
    }

    public function export(Request $request)
    {
        $query = SessionLog::with('user')
            ->orderBy('login_time', 'desc')
            ->limit(500);

        if ($search = $request->search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        if ($role = $request->role) {
            $query->whereHas('user', function ($q) use ($role) {
                $q->where('user_type', $role);
            });
        }

        $headers = ['Name', 'Email', 'Role', 'Login Time', 'Logout Time', 'Duration', 'Activity'];

        $callback = function ($log) {
            $userType = $log->user->user_type ?? '';
            return [
                $log->user->full_name ?? 'Unknown',
                $log->user->email ?? '',
                is_object($userType) ? $userType->value : $userType,
                $log->login_time,
                $log->logout_time ?? '',
                $this->formatDuration($log->login_time, $log->logout_time),
                trim(preg_replace('/\s+/', ' ', $log->activity ?? ''))
            ];
        };

        return $this->streamCsvExport(
            $query,
            $headers,
            $callback,
            'activity_ledger_' . now()->format('Y-m-d') . '.csv'
        );
    }

    private function formatDuration($login, $logout)
    {
        if (!$logout) return 'LIVE';
        $diff = strtotime($logout) - strtotime($login);
        if ($diff < 60) return $diff . 's';
        if ($diff < 3600) return round($diff / 60) . 'm';
        return round($diff / 3600, 1) . 'h';
    }
}
