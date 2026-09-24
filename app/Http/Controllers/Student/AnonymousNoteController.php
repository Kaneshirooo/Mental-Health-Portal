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

        DB::transaction(function () use ($request) {
            $note = AnonymousNote::create([
                'student_id' => auth()->id(),
                'status' => 'new',
            ]);

            AnonymousNoteMessage::create([
                'note_id' => $note->note_id,
                'sender_type' => 'student',
                'message_text' => $request->message,
            ]);

            // Notify Clinical Staff (optimized)
            $studentName = auth()->user()->full_name;
            $staff = \App\Models\User::whereIn('user_type', ['admin', 'counselor'])->get();
            foreach ($staff as $member) {
                \App\Models\Notification::create([
                    'user_id' => $member->user_id,
                    'title' => 'New Clinical Note',
                    'message' => "{$studentName} shared a new clinical note.",
                    'type' => 'note'
                ]);
            }
        });

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Your note has been sent to our clinical team.',
                'date' => now()->format('M d, Y'),
                'time' => now()->format('g:i A')
            ]);
        }

        return back()->with('success', 'Your note has been sent to our clinical team.');
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
                'type' => 'note'
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
