<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AnonymousNote;
use App\Models\AnonymousNoteMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnonymousNoteController extends Controller
{
    public function index()
    {
        $notes = AnonymousNote::where('student_id', auth()->id())
            ->with(['messages' => function($query) {
                $query->orderBy('created_at', 'asc');
            }])
            ->latest()
            ->get();

        return view('student.notes.index', compact('notes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        // Record every statement so a failure can be traced to the first thing
        // that actually went wrong instead of the 25P02 cascade it leaves behind.
        $executedStatements = [];
        DB::listen(function ($query) use (&$executedStatements) {
            $executedStatements[] = $query->sql;
        });

        // Start the transaction clean and retry once on the 25P02 cascade: a
        // connection that arrives already aborted makes the first INSERT fail
        // with "current transaction is aborted" and hides the real cause.
        $attempt = 0;
        $maxAttempts = 2;

        while (true) {
            $attempt++;

            try {
                $this->beginCleanTransaction();

                $note = AnonymousNote::create([
                    'student_id' => auth()->id(),
                    'status' => 'new',
                ]);

                AnonymousNoteMessage::create([
                    'note_id' => $note->note_id,
                    'sender_type' => 'student',
                    'message_text' => $request->message,
                ]);

                // Notify counselors
                $studentName = auth()->user()->full_name;
                $staff = \App\Models\User::whereIn('user_type', ['admin', 'counselor'])->get();
                foreach ($staff as $member) {
                    \App\Models\Notification::create([
                        'user_id' => $member->user_id,
                        'title' => 'New Student Note',
                        'message' => "{$studentName} shared a new note.",
                        'type' => 'note',
                        'related_id' => $note->note_id,
                    ]);
                }

                DB::commit();
                break;
            } catch (\Throwable $e) {
                try {
                    DB::rollBack();
                } catch (\Throwable $ignore) {
                    // The transaction was already gone; nothing to undo.
                }

                $diagnostics = [
                    'attempt' => $attempt,
                    'sqlState' => $e->getCode(),
                    'transactionLevel' => DB::transactionLevel(),
                    'error' => $e->getMessage(),
                    'statements' => $executedStatements,
                ];
                $this->debugLog('H4', 'AnonymousNoteController.php:store', 'Note store failed', $diagnostics);
                \Illuminate\Support\Facades\Log::error('Anonymous note store failed', $diagnostics);

                if ($attempt < $maxAttempts && $this->isAbortedTransactionCascade($e)) {
                    continue;
                }

                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Could not send your note: ' . $e->getMessage(),
                    ], 500);
                }

                return back()->with('error', 'Failed to send your note. Please try again.');
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Your note has been sent to your counselor.',
                'date' => now()->format('M d, Y'),
                'time' => now()->format('g:i A')
            ]);
        }

        return back()->with('success', 'Your note has been sent to your counselor.');
    }

    public function reply(Request $request, AnonymousNote $note)
    {
        if ($note->student_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'reply_text' => 'required|string',
        ]);

        AnonymousNoteMessage::create([
            'note_id' => $note->note_id,
            'sender_type' => 'student',
            'message_text' => $request->reply_text,
            'created_at' => now(),
        ]);

        $note->update(['status' => 'replied']);

        // Notify counselor if assigned
        if ($note->counselor_id) {
            \App\Models\Notification::create([
                'user_id' => $note->counselor_id,
                'title' => 'Note Follow-up',
                'message' => auth()->user()->full_name . " replied to Note #" . str_pad($note->note_id, 3, '0', STR_PAD_LEFT),
                'type' => 'note',
                'related_id' => $note->note_id,
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Your follow-up has been sent.',
                'time' => now()->format('g:i A')
            ]);
        }

        return back()->with('success', 'Your follow-up has been sent.');
    }
}
