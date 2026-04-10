<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\AnonymousNote;
use App\Models\AnonymousNoteMessage;
use Illuminate\Http\Request;

class AnonymousNoteController extends Controller
{
    public function index()
    {
        $notes = AnonymousNote::with(['messages' => function($query) {
                $query->orderBy('created_at', 'asc');
            }])
            ->whereIn('status', ['new', 'read', 'replied'])
            ->latest()
            ->get();

        return view('counselor.notes.index', compact('notes'));
    }

    public function reply(Request $request, AnonymousNote $note)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        AnonymousNoteMessage::create([
            'note_id' => $note->note_id,
            'sender_type' => 'counselor',
            'message_text' => $request->message,
        ]);

        $note->update(['status' => 'replied']);

        return back()->with('success', 'Your reply has been sent.');
    }

    public function updateStatus(Request $request, AnonymousNote $note)
    {
        $request->validate([
            'status' => 'required|in:read,closed',
        ]);

        $note->update(['status' => $request->status]);

        return back()->with('success', 'Note status updated.');
    }
}
