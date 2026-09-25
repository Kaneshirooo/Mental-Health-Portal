<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\EmergencyCall;
use App\Models\CallMessage;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmergencyCallController extends Controller
{
    /**
     * Returns all pending emergency calls ordered FIFO by call_id.
     */
    public function pending()
    {
        // Clean up any stale/unanswered pending calls first
        EmergencyCall::cleanupStaleCalls(90);

        $pendingCalls = EmergencyCall::where('status', 'pending')
            ->where('created_at', '>=', now()->subSeconds(90))
            ->with(['student'])
            ->orderBy('call_id', 'asc')
            ->get();

        $calls = $pendingCalls->values()->map(function ($c, $index) use ($pendingCalls) {
            return [
                'call_id'        => $c->call_id,
                'student_name'   => $c->student->full_name ?? 'Unknown',
                'roll_number'    => $c->student->roll_number ?? 'N/A',
                'risk_level'     => $c->student->latestAssessment?->risk_level ?? 'High',
                'overall_score'  => $c->student->latestAssessment?->overall_score ?? '—',
                'created_at'     => $c->created_at->diffForHumans(),
                'queue_position' => $index + 1,
                'total_queued'   => $pendingCalls->count(),
            ];
        });

        return response()->json(['calls' => $calls]);
    }

    /**
     * Counselor accepts a pending call.
     */
    public function accept(EmergencyCall $call)
    {
        if (!$call->isPending()) {
            return response()->json(['error' => 'Call is no longer pending.'], 409);
        }

        $counselor = Auth::user();

        $call->update([
            'counselor_id' => $counselor->user_id,
            'status'       => 'active',
            'started_at'   => now(),
        ]);

        // Notify the student
        Notification::create([
            'user_id' => $call->student_id,
            'title'   => '✅ Counselor Accepted Your Call',
            'message' => "{$counselor->full_name} has accepted your emergency call request. The chat is now open.",
            'type'    => 'emergency',
        ]);

        return response()->json([
            'success' => true,
            'call'    => [
                'call_id'       => $call->call_id,
                'student_name'  => $call->student->full_name,
                'roll_number'   => $call->student->roll_number ?? 'N/A',
                'risk_level'    => $call->student->latestAssessment?->risk_level ?? 'High',
                'overall_score' => $call->student->latestAssessment?->overall_score ?? '—',
            ]
        ]);
    }

    /**
     * Counselor declines a pending call.
     */
    public function decline(EmergencyCall $call)
    {
        $call->update(['status' => 'declined', 'ended_at' => now()]);

        Notification::create([
            'user_id' => $call->student_id,
            'title'   => 'Call Unavailable',
            'message' => 'The counselor is currently unable to take your call. Please try again or visit the appointments page.',
            'type'    => 'emergency',
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Counselor ends an active call.
     */
    public function end(EmergencyCall $call, Request $request)
    {
        $user = Auth::user();
        $userType = $user->user_type->value ?? (string) $user->user_type;
        $isParticipant = ($call->counselor_id == $user->user_id) || ($call->student_id == $user->user_id) || in_array($userType, ['counselor', 'admin']);

        if (!$isParticipant) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return redirect()->back()->with('error', 'Unauthorized to end call.');
        }

        $call->update(['status' => 'ended', 'ended_at' => now()]);

        Notification::create([
            'user_id' => $call->student_id,
            'title'   => 'Call Ended',
            'message' => "Your emergency counseling call has been ended. You may book a follow-up appointment anytime.",
            'type'    => 'emergency',
        ]);

        // Notify next queued student and available counselors
        $this->notifyQueuedStudentAndCounselors();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Emergency call session successfully ended.');
    }

    /**
     * Counselor checks active call status.
     */
    public function status(EmergencyCall $call)
    {
        session()->save();
        // Allow both the assigned counselor and any admin to check status
        $userId = Auth::id();
        if ($call->counselor_id != $userId && Auth::user()->user_type->value !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'status'       => $call->status,
            'student_name' => $call->student?->full_name,
            'student_id'   => $call->student_id,
            'counselor_id' => $call->counselor_id,
        ]);
    }

    /**
     * Counselor sends a message in the call chat.
     */
    public function sendMessage(Request $request, EmergencyCall $call)
    {
        $userId = Auth::id();
        session()->save();
        $userType = Auth::user()->user_type->value ?? '';
        
        \Illuminate\Support\Facades\Log::info("Counselor EmergencyCallController@sendMessage entered", [
            'call_id' => $call->call_id,
            'counselor_id' => $call->counselor_id,
            'auth_id' => $userId,
            'user_type' => $userType,
            'has_message' => $request->has('message'),
            'message_length' => strlen($request->input('message', ''))
        ]);

        // Allow: the assigned counselor OR any counselor/admin (call may still be pending when they first send a signal)
        $isParticipant = $call->counselor_id == $userId
            || in_array($userType, ['counselor', 'admin']);

        if (!$isParticipant) {
            \Illuminate\Support\Facades\Log::warning("Counselor EmergencyCallController@sendMessage unauthorized", [
                'call_id' => $call->call_id,
                'counselor_id' => $call->counselor_id,
                'auth_id' => $userId,
                'user_type' => $userType
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate(['message' => 'required|string|max:20000']);

        $msg = CallMessage::create([
            'call_id'      => $call->call_id,
            'sender_id'    => $userId,
            'message_text' => $request->message,
            'created_at'   => now(),
        ]);

        \Illuminate\Support\Facades\Log::info("Counselor EmergencyCallController@sendMessage success", [
            'call_id' => $call->call_id,
            'message_id' => $msg->message_id,
            'sender_id' => $msg->sender_id
        ]);

        return response()->json([
            'success'    => true,
            'message_id' => $msg->message_id,
        ]);
    }

    /**
     * Counselor polls for new messages.
     */
    public function getMessages(Request $request, EmergencyCall $call)
    {
        session()->save();
        $userId = Auth::id();
        $userType = Auth::user()->user_type->value ?? '';
        $afterId = $request->get('after_id', 0);

        \Illuminate\Support\Facades\Log::info("Counselor EmergencyCallController@getMessages entered", [
            'call_id' => $call->call_id,
            'counselor_id' => $call->counselor_id,
            'auth_id' => $userId,
            'user_type' => $userType,
            'after_id' => $afterId
        ]);

        $isParticipant = $call->counselor_id == $userId
            || in_array($userType, ['counselor', 'admin']);

        if (!$isParticipant) {
            \Illuminate\Support\Facades\Log::warning("Counselor EmergencyCallController@getMessages unauthorized", [
                'call_id' => $call->call_id,
                'counselor_id' => $call->counselor_id,
                'auth_id' => $userId,
                'user_type' => $userType
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = CallMessage::where('call_id', $call->call_id)
            ->where('message_id', '>', $afterId)
            ->with('sender:user_id,full_name,user_type')
            ->orderBy('message_id', 'asc')
            ->get()
            ->map(function ($m) use ($call) {
                $isStudent = ((int) $m->sender_id === (int) $call->student_id);
                $senderType = $m->sender?->user_type?->value ?? (string) ($m->sender?->user_type ?? ($isStudent ? 'student' : 'counselor'));
                $senderName = $m->sender?->full_name ?? ($isStudent ? ($call->student?->full_name ?? 'Student') : ($call->counselor?->full_name ?? 'Counselor'));
                return [
                    'message_id'   => $m->message_id,
                    'sender_id'    => $m->sender_id,
                    'sender_name'  => $senderName,
                    'sender_type'  => strtolower($senderType),
                    'message_text' => $m->message_text,
                    'created_at'   => $m->created_at ? $m->created_at->format('h:i A') : now()->format('h:i A'),
                ];
            });

        \Illuminate\Support\Facades\Log::info("Counselor EmergencyCallController@getMessages success", [
            'call_id' => $call->call_id,
            'count' => $messages->count()
        ]);

        return response()->json(['messages' => $messages]);
    }

    /**
     * Counselor reviews full conversation history of an emergency call.
     */
    public function history(EmergencyCall $call): View
    {
        $call->loadMissing([
            'student:user_id,full_name,roll_number',
            'counselor:user_id,full_name',
        ]);

        $user = Auth::user();
        $userType = $user->user_type->value ?? $user->user_type ?? '';
        
        $isParticipant = ($call->student_id == $user->user_id) || ($call->counselor_id == $user->user_id) || in_array($userType, ['counselor', 'admin']);
        if (!$isParticipant) {
            abort(403, 'Unauthorized access to clinical session history.');
        }

        $messages = CallMessage::where('call_id', $call->call_id)
            ->with('sender:user_id,full_name,user_type')
            ->orderBy('message_id')
            ->get();

        $postSessionAssessment = $messages->first(function ($m) {
            return str_starts_with((string) $m->message_text, '[AI Post-Session Assessment]');
        });

        // Calculate session duration
        $duration = null;
        if ($call->started_at && $call->ended_at) {
            $diff = $call->started_at->diff($call->ended_at);
            $duration = ($diff->h > 0 ? $diff->h . 'h ' : '') . $diff->i . 'm ' . $diff->s . 's';
        }

        return view('counselor.emergency_calls.history', [
            'call'                 => $call,
            'messages'             => $messages,
            'postSessionAssessment' => $postSessionAssessment,
            'duration'             => $duration,
        ]);
    }

    /**
     * Display a listing of emergency calls history/logs.
     */
    public function logs(Request $request): View
    {
        // Clean up stale calls first
        EmergencyCall::cleanupStaleCalls(90);

        $user = Auth::user();
        $userType = $user->user_type->value ?? $user->user_type ?? '';

        $query = EmergencyCall::with(['student', 'counselor'])
            ->orderBy('created_at', 'desc');

        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($userType === 'counselor') {
            if ($request->has('my_calls')) {
                $query->where('counselor_id', $user->user_id);
            }
        }

        $calls = $query->paginate(15);

        return view('counselor.emergency_calls.logs', compact('calls'));
    }

    /**
     * Notify the next student in the queue and all emergency-available counselors
     * that a counselor slot has opened up.
     */
    private function notifyQueuedStudentAndCounselors(): void
    {
        $nextCall = EmergencyCall::where('status', 'pending')
            ->orderBy('call_id', 'asc')
            ->first();

        if (!$nextCall) {
            return;
        }

        Notification::create([
            'user_id' => $nextCall->student_id,
            'title'   => '✅ A Counselor is Now Available',
            'message' => 'A counselor has finished their previous session and is now available. A counselor will connect with you shortly — please stay on the line.',
            'type'    => 'emergency',
        ]);

        $counselors = User::whereIn('user_type', ['counselor', 'admin'])
            ->where('is_emergency_available', true)
            ->get();

        $student = $nextCall->student;
        foreach ($counselors as $counselor) {
            Notification::create([
                'user_id' => $counselor->user_id,
                'title'   => '🚨 Queued Student Waiting',
                'message' => ($student?->full_name ?? 'A student') . ' is next in the emergency queue and is waiting for a counselor. Please accept the call from your dashboard.',
                'type'    => 'emergency',
            ]);
        }
    }
}

