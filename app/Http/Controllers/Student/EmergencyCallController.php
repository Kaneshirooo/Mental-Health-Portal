<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\EmergencyCall;
use App\Models\CallMessage;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmergencyCallController extends Controller
{
    /**
     * Student initiates an emergency call.
     * Creates a pending call and notifies all available counselors.
     */
    public function request(Request $request)
    {
        $student = Auth::user();

        // Check if at least one counselor is available AND currently online in the system
        if (!User::hasAvailableOnlineCounselors()) {
            return response()->json([
                'success' => false,
                'message' => 'No counselors are currently online or using the system. Please schedule an appointment or reach out to emergency hotlines.',
            ], 422);
        }

        // Cancel any existing pending/active calls for this student first
        EmergencyCall::where('student_id', $student->user_id)
            ->whereIn('status', ['pending', 'active'])
            ->update(['status' => 'ended', 'ended_at' => now()]);

        $call = EmergencyCall::create([
            'student_id'  => $student->user_id,
            'counselor_id' => null,
            'status'      => 'pending',
        ]);

        // Notify ALL counselors who are marked as emergency-available AND online
        $counselors = User::whereIn('user_type', ['counselor', 'admin'])
            ->where('is_emergency_available', true)
            ->get()
            ->filter(fn(\App\Models\User $c) => $c->isOnline());

        foreach ($counselors as $counselor) {
            Notification::create([
                'user_id' => $counselor->user_id,
                'title'   => '🚨 Emergency Call Request',
                'message' => "{$student->full_name} is requesting an immediate counseling call. Please respond on your dashboard.",
                'type'    => 'emergency',
            ]);
        }

        return response()->json([
            'success' => true,
            'call_id' => $call->call_id,
            'message' => 'Call request sent. Please wait for a counselor to accept.',
        ]);
    }

    /**
     * Student polls for their call status and queue position.
     */
    public function status(EmergencyCall $call)
    {
        session()->save();
        if ($call->student_id != Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $queuePosition = 0;
        $totalQueued = 0;
        $counselorsBusy = false;
        $counselorsOnline = User::hasAvailableOnlineCounselors();

        EmergencyCall::cleanupStaleCalls(90);
        $call->refresh();

        if ($call->status === 'pending') {
            // FIFO Queue position based on call ID order
            $queuePosition = EmergencyCall::where('status', 'pending')
                ->where('call_id', '<=', $call->call_id)
                ->count();

            $totalQueued = EmergencyCall::where('status', 'pending')->count();

            // Check if emergency-available counselors are currently occupied in active calls
            $totalCounselors = User::whereIn('user_type', ['counselor', 'admin'])
                ->where('is_emergency_available', true)
                ->get()
                ->filter(fn(\App\Models\User $c) => $c->isOnline())
                ->count();

            $busyCounselors = EmergencyCall::where('status', 'active')
                ->whereNotNull('counselor_id')
                ->distinct('counselor_id')
                ->count('counselor_id');

            $counselorsBusy = ($totalCounselors > 0 && $busyCounselors >= $totalCounselors) || $queuePosition > 1;
        }

        return response()->json([
            'status'            => $call->status,
            'counselor_name'    => $call->counselor?->full_name,
            'counselor_id'      => $call->counselor_id,
            'queue_position'    => $queuePosition,
            'total_queued'      => $totalQueued,
            'counselors_busy'   => $counselorsBusy,
            'counselors_online' => $counselorsOnline,
        ]);
    }

    /**
     * Student ends the call.
     */
    public function end(EmergencyCall $call)
    {
        if ($call->student_id != Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $call->update(['status' => 'ended', 'ended_at' => now()]);

        // Notify the counselor if one was assigned
        if ($call->counselor_id) {
            Notification::create([
                'user_id' => $call->counselor_id,
                'title'   => 'Call Ended',
                'message' => "The emergency call with {$call->student->full_name} has ended.",
                'type'    => 'emergency',
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Student sends a message in the call chat.
     */
    public function sendMessage(Request $request, EmergencyCall $call)
    {
        $userId = Auth::id();
        \Illuminate\Support\Facades\Log::info("Student EmergencyCallController@sendMessage entered", [
            'call_id' => $call->call_id,
            'student_id' => $call->student_id,
            'auth_id' => $userId,
            'has_message' => $request->has('message'),
            'message_length' => strlen($request->input('message', ''))
        ]);

        if ($call->student_id != $userId) {
            \Illuminate\Support\Facades\Log::warning("Student EmergencyCallController@sendMessage unauthorized", [
                'call_id' => $call->call_id,
                'student_id' => $call->student_id,
                'auth_id' => $userId
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

        \Illuminate\Support\Facades\Log::info("Student EmergencyCallController@sendMessage success", [
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
     * Student polls for new messages.
     */
    public function getMessages(Request $request, EmergencyCall $call)
    {
        session()->save();
        $userId = Auth::id();
        $afterId = $request->get('after_id', 0);
        
        \Illuminate\Support\Facades\Log::info("Student EmergencyCallController@getMessages entered", [
            'call_id' => $call->call_id,
            'student_id' => $call->student_id,
            'auth_id' => $userId,
            'after_id' => $afterId
        ]);

        if ($call->student_id != $userId) {
            \Illuminate\Support\Facades\Log::warning("Student EmergencyCallController@getMessages unauthorized", [
                'call_id' => $call->call_id,
                'student_id' => $call->student_id,
                'auth_id' => $userId
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

        \Illuminate\Support\Facades\Log::info("Student EmergencyCallController@getMessages success", [
            'call_id' => $call->call_id,
            'count' => $messages->count()
        ]);

        return response()->json(['messages' => $messages]);
    }
}
