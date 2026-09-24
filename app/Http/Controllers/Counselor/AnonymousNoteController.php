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

        $note->update([
            'status' => 'replied',
            'counselor_id' => \Illuminate\Support\Facades\Auth::id()
        ]);

        // Notify student about counselor's reply
        if ($note->student_id) {
            \App\Models\Notification::create([
                'user_id' => $note->student_id,
                'title' => 'Counselor Note Reply 💬',
                'message' => 'A counselor has replied to your Note #' . str_pad($note->note_id, 3, '0', STR_PAD_LEFT) . '.',
                'type' => 'note',
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Your reply has been sent.'
            ]);
        }

        return back()->with('success', 'Your reply has been sent.');
    }

    public function updateStatus(Request $request, AnonymousNote $note)
    {
        $request->validate([
            'status' => 'required|in:read,closed',
        ]);

        $note->update(['status' => $request->status]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Note status updated.'
            ]);
        }

        return back()->with('success', 'Note status updated.');
    }
}
