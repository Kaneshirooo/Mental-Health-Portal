<?php

namespace App\Http\Controllers;

use App\Models\EmergencyCall;
use App\Models\CallMessage;
use App\Models\Notification;
use App\Models\User;
use App\Services\OpenRouterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoCallController extends Controller
{
    private function agentDebugLog(string $hypothesisId, string $location, string $message, array $data = [], string $runId = 'initial'): void
    {
        // #region agent log
        file_put_contents(
            base_path('debug-d2d10b.log'),
            json_encode([
                'sessionId' => 'd2d10b',
                'runId' => $runId,
                'hypothesisId' => $hypothesisId,
                'location' => $location,
                'message' => $message,
                'data' => $data,
                'timestamp' => (int) round(microtime(true) * 1000),
            ], JSON_UNESCAPED_SLASHES) . PHP_EOL,
            FILE_APPEND
        );
        // #endregion
    }

    /**
     * Show the dedicated video call page.
     */
    public function show(EmergencyCall $call)
    {
        $user = Auth::user();
        
        // Security: Ensure user is part of the call (allows counselors to view/join pending calls)
        if (!$this->isCallParticipant($call, (int) $user->user_id)) {
            abort(403, 'Unauthorized access to clinical session.');
        }

        // Security: Ensure call is active or pending (if counselor hasn't joined yet)
        if (in_array($call->status, ['ended', 'declined', 'missed'], true)) {
            $isStudent = strtolower((string) ($user->user_type->value ?? $user->user_type)) === 'student';
            $msg = $call->status === 'missed' ? 'This emergency call went unanswered as no counselor was available.' : 'The clinical session has already ended.';
            return redirect()->route($isStudent ? 'student.dashboard' : 'counselor.dashboard')
                ->with('error', $msg);
        }

        $userType = strtolower((string) ($user->user_type->value ?? $user->user_type));
        if (in_array($userType, ['counselor', 'admin'], true) && $call->status === 'pending') {
            $call->update([
                'counselor_id' => $user->user_id,
                'status'       => 'active',
                'started_at'   => now(),
            ]);
            $call->refresh();
        }

        return view('video-call', compact('call', 'user'));
    }

    /**
     * Shared send endpoint so student and counselor write to the same call thread.
     */
    public function sendMessage(Request $request, EmergencyCall $call)
    {
        $user = Auth::user();
        session_write_close();
        if (!$this->isCallParticipant($call, $user->user_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate(['message' => 'required|string|max:20000']);

        $msg = CallMessage::create([
            'call_id'      => $call->call_id,
            'sender_id'    => $user->user_id,
            'message_text' => $request->input('message'),
            'created_at'   => now(),
        ]);

        $userType = $user->user_type->value ?? (string) $user->user_type;

        return response()->json([
            'success'      => true,
            'message_id'   => $msg->message_id,
            'sender_id'    => $user->user_id,
            'sender_name'  => $user->full_name,
            'sender_type'  => strtolower($userType),
            'message_text' => $msg->message_text,
            'created_at'   => now()->format('h:i A'),
        ]);
    }

    /**
     * Shared poll endpoint. Use channel=chat or channel=signal so huge WebRTC
     * payloads cannot hide or stall the other person's chat messages.
     */
    public function getMessages(Request $request, EmergencyCall $call)
    {
        session_write_close();
        $user = Auth::user();
        if (!$this->isCallParticipant($call, $user->user_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $afterId = (int) $request->get('after_id', 0);
        $channel = $request->get('channel', 'all');

        $query = CallMessage::where('call_id', $call->call_id)
            ->where('message_id', '>', $afterId)
            ->with('sender:user_id,full_name,user_type')
            ->orderBy('message_id', 'asc');

        if ($channel === 'chat') {
            $query->where('message_text', 'not like', '%webrtc_signal%');
        } elseif ($channel === 'signal') {
            $query->where('message_text', 'like', '%webrtc_signal%');
        }

        $messages = $query->limit($channel === 'signal' ? 40 : 150)->get()->map(function ($m) use ($call) {
            $senderType = $m->sender?->user_type?->value ?? (string) ($m->sender?->user_type ?? '');
            $isStudent  = ((int) $m->sender_id === (int) $call->student_id);
            if (!$senderType) {
                $senderType = $isStudent ? 'student' : 'counselor';
            }
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

        return response()->json([
            'messages'          => $messages,
            'call_status'       => $call->status,
            'counselor_name'    => $call->counselor?->full_name,
            'counselor_id'      => $call->counselor_id,
            'student_name'      => $call->student?->full_name,
            'student_id'        => $call->student_id,
            'counselors_online' => User::hasAvailableOnlineCounselors(),
        ]);
    }

    /**
     * Terminate the current clinical session for either participant.
     */
    public function terminate(EmergencyCall $call)
    {
        $start = microtime(true);
        $user = Auth::user();
        $isStudent = $user->user_id == $call->student_id;
        $isCounselor = $user->user_id == $call->counselor_id;
        $this->agentDebugLog('H3', 'VideoCallController.php:terminate', 'Terminate endpoint entered', [
            'callId' => $call->call_id,
            'userId' => $user->user_id,
            'isStudent' => $isStudent,
            'isCounselor' => $isCounselor,
            'statusBefore' => $call->status,
        ]);

        if (!$isStudent && !$isCounselor) {
            $this->agentDebugLog('H4', 'VideoCallController.php:terminate', 'Terminate rejected unauthorized participant', [
                'callId' => $call->call_id,
                'userId' => $user->user_id,
            ]);
            return response()->json(['error' => 'Unauthorized access to clinical session.'], 403);
        }

        if (!in_array($call->status, ['ended', 'declined'], true)) {
            $call->update([
                'status' => 'ended',
                'ended_at' => now(),
            ]);
        }

        // ── Queue Notification ─────────────────────────────────────────────
        // When a session ends, check if any students are still queued (pending).
        // If yes, notify the next student in line AND all available counselors.
        $this->notifyQueuedStudentAndCounselors($call);
        // ──────────────────────────────────────────────────────────────────

        $callId = $call->call_id;
        $userId = $user->user_id;

        // Register post-session AI assessment to run after response is sent
        register_shutdown_function(function () use ($callId, $userId) {
            try {
                $c = EmergencyCall::find($callId);
                if ($c) {
                    app(VideoCallController::class)->runPostSessionAssessment($c, $userId);
                }
            } catch (\Throwable $e) {
                // Ignore background assessment exceptions
            }
        });

        $this->agentDebugLog('H3', 'VideoCallController.php:terminate', 'Terminate completed instantly', [
            'callId' => $call->call_id,
            'statusAfter' => $call->status,
            'durationMs' => (int) round((microtime(true) - $start) * 1000),
        ]);

        session_write_close();
        if (function_exists('fastcgi_finish_request')) {
            response()->json(['success' => true, 'status' => $call->status])->send();
            fastcgi_finish_request();
            app(VideoCallController::class)->runPostSessionAssessment($call, $userId);
            exit;
        }

        return response()->json([
            'success' => true,
            'status' => $call->status,
        ]);
    }

    /**
     * Run AI assessment after the call ends and persist result to call chat.
     */
    public function runPostSessionAssessment(EmergencyCall $call, int $actorId): void
    {
        $assessmentStart = microtime(true);
        $alreadyAssessed = CallMessage::where('call_id', $call->call_id)
            ->where('message_text', 'like', '[AI Post-Session Assessment]%')
            ->exists();
        if ($alreadyAssessed) {
            $this->agentDebugLog('H5', 'VideoCallController.php:runPostSessionAssessment', 'Assessment skipped because already exists', [
                'callId' => $call->call_id,
            ]);
            return;
        }

        $messages = CallMessage::where('call_id', $call->call_id)
            ->with('sender:user_id,full_name,user_type')
            ->orderBy('message_id')
            ->limit(120)
            ->get()
            ->filter(function ($m) {
                $text = trim((string) $m->message_text);
                return $text !== '' && !str_contains($text, '"kind":"webrtc_signal"');
            })
            ->values();

        if ($messages->isEmpty()) {
            $this->agentDebugLog('H5', 'VideoCallController.php:runPostSessionAssessment', 'Assessment skipped due empty messages', [
                'callId' => $call->call_id,
            ]);
            return;
        }

        $transcript = $messages->map(function ($m) {
            $speaker = $m->sender?->full_name ?? 'Participant';
            return "{$speaker}: {$m->message_text}";
        })->implode("\n");

        /** @var OpenRouterService $openRouterService */
        $openRouterService = app(OpenRouterService::class);
        $systemInstruction = "You are a clinical support assistant. Analyze counseling conversation text and return STRICT JSON only.";
        $prompt = "Analyze this emergency counseling conversation transcript.\n\n"
            . "Return strictly valid JSON with keys:\n"
            . "summary (string),\n"
            . "risk_level (one of: Low, Moderate, High, Critical),\n"
            . "counselor_suggestions (array of 3 concise strings),\n"
            . "immediate_actions (array of up to 3 concise strings).\n\n"
            . "Conversation:\n{$transcript}";

        $raw = $openRouterService->generateResponse([
            ['role' => 'user', 'parts' => [['text' => $prompt]]],
        ], $systemInstruction);
        $this->agentDebugLog('H5', 'VideoCallController.php:runPostSessionAssessment', 'OpenRouter response returned', [
            'callId' => $call->call_id,
            'rawLength' => strlen($raw),
        ]);

        $analysis = $this->parseAnalysisJson($raw);
        $suggestions = implode('; ', $analysis['counselor_suggestions']);
        $actions = implode('; ', $analysis['immediate_actions']);
        $assessmentText = "[AI Post-Session Assessment]\n"
            . "Risk: {$analysis['risk_level']}\n"
            . "Summary: {$analysis['summary']}\n"
            . "Suggestions: " . ($suggestions !== '' ? $suggestions : 'None') . "\n"
            . "Immediate actions: " . ($actions !== '' ? $actions : 'None');

        CallMessage::create([
            'call_id' => $call->call_id,
            'sender_id' => $actorId,
            'message_text' => $assessmentText,
        ]);
        $this->agentDebugLog('H5', 'VideoCallController.php:runPostSessionAssessment', 'Assessment saved to call messages', [
            'callId' => $call->call_id,
            'durationMs' => (int) round((microtime(true) - $assessmentStart) * 1000),
        ]);
    }

    private function parseAnalysisJson(string $raw): array
    {
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            $start = strpos($raw, '{');
            $end = strrpos($raw, '}');
            if ($start !== false && $end !== false && $end > $start) {
                $slice = substr($raw, $start, ($end - $start + 1));
                $decoded = json_decode($slice, true);
            }
        }

        if (!is_array($decoded)) {
            return [
                'summary' => 'AI analysis completed, but response formatting was invalid.',
                'risk_level' => 'Moderate',
                'counselor_suggestions' => [],
                'immediate_actions' => [],
            ];
        }

        return [
            'summary' => (string) ($decoded['summary'] ?? 'No summary generated.'),
            'risk_level' => (string) ($decoded['risk_level'] ?? 'Moderate'),
            'counselor_suggestions' => array_values(array_filter((array) ($decoded['counselor_suggestions'] ?? []), 'is_string')),
            'immediate_actions' => array_values(array_filter((array) ($decoded['immediate_actions'] ?? []), 'is_string')),
        ];
    }

    private function isCallParticipant(EmergencyCall $call, int $userId): bool
    {
        // Always allow the student of this specific call
        if ($userId === (int) $call->student_id) return true;

        // Allow the assigned counselor
        if ($call->counselor_id && $userId === (int) $call->counselor_id) return true;

        // Allow any counselor/admin — call may be pending (counselor_id is null when
        // a counselor joins the page before accepting, e.g. viewing a pending call)
        $user = Auth::user();
        $userType = $user->user_type->value ?? (string) $user->user_type;
        if (in_array($userType, ['counselor', 'admin'])) return true;

        return false;
    }

    /**
     * After a session ends, notify the next queued student (if any)
     * and all emergency-available counselors that a slot has opened.
     */
    private function notifyQueuedStudentAndCounselors(EmergencyCall $endedCall): void
    {
        $nextCall = EmergencyCall::where('status', 'pending')
            ->orderBy('call_id', 'asc')
            ->first();

        if (!$nextCall) {
            return; // No students waiting
        }

        // Notify the queued student
        Notification::create([
            'user_id' => $nextCall->student_id,
            'title'   => '✅ A Counselor is Now Available',
            'message' => 'A counselor has finished their previous session and is now available. A counselor will connect with you shortly — please stay on the line.',
            'type'    => 'emergency',
        ]);

        // Notify all emergency-available counselors so they see the queued student
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
