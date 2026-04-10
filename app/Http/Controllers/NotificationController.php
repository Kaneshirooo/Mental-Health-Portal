<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        // Mark all as read on page visit
        Notification::where('user_id', auth()->id())->update(['is_read' => 1]);

        $notifications = Notification::where('user_id', auth()->id())
            ->latest('created_at')
            ->limit(50)
            ->get();

        return view('notifications.index', compact('notifications'));
    }

    public function count()
    {
        $count = Notification::where('user_id', auth()->id())
            ->where('is_read', 0)
            ->count();

        return response()->json(['count' => $count]);
    }

    public function clearAll()
    {
        Notification::where('user_id', auth()->id())->delete();
        return response()->json(['success' => true]);
    }
}
