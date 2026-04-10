<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\CounselorAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AvailabilityController extends Controller
{
    public function index()
    {
        $days = [
            ['id' => 1, 'name' => 'Monday'],
            ['id' => 2, 'name' => 'Tuesday'],
            ['id' => 3, 'name' => 'Wednesday'],
            ['id' => 4, 'name' => 'Thursday'],
            ['id' => 5, 'name' => 'Friday'],
            ['id' => 6, 'name' => 'Saturday'],
            ['id' => 0, 'name' => 'Sunday'],
        ];
        $slots = CounselorAvailability::where('counselor_id', Auth::id())
            ->where('is_active', 1)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
            
        return view('counselor.availability', compact('slots', 'days'));
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            // Delete existing slots
            CounselorAvailability::where('counselor_id', Auth::id())->delete();

            if ($request->has('slots') && is_array($request->slots)) {
                foreach ($request->slots as $slot) {
                    $day = intval($slot['day'] ?? -1);
                    $start = $slot['start'] ?? '';
                    $end = $slot['end'] ?? '';

                    if ($day >= 0 && $day <= 6 && $start && $end && $start < $end) {
                        CounselorAvailability::create([
                            'counselor_id' => Auth::id(),
                            'day_of_week' => $day,
                            'start_time' => $start,
                            'end_time' => $end,
                            'is_active' => 1
                        ]);
                    }
                }
            }
        });

        return redirect()->route('counselor.availability')
            ->with('success', 'Availability updated successfully.');
    }
}
