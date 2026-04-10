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
        });

        return back()->with('success', 'Your note has been sent anonymously.');
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
        ]);

        $note->update(['status' => 'new']);

        return back()->with('success', 'Your reply has been sent.');
    }
}
