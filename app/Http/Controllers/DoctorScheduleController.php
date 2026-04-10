<?php

namespace App\Http\Controllers;

use App\Models\DoctorSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorScheduleController extends Controller
{
    /**
     * Get the doctor's weekly schedule.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user->isApprovedDoctor()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $schedules = $user->doctorSchedules()->orderBy('day_of_week')->get();

        // If no schedule exists, return a default template (Mon-Sun)
        if ($schedules->isEmpty()) {
            $default = [];
            for ($i = 1; $i <= 7; $i++) {
                $day = ($i === 7) ? 0 : $i; // Sunday is 0
                $default[] = [
                    'day_of_week' => $day,
                    'start_time' => '08:00:00',
                    'end_time' => '17:00:00',
                    'is_active' => false,
                ];
            }
            return response()->json(['schedules' => $default]);
        }

        return response()->json(['schedules' => $schedules]);
    }

    /**
     * Update or create schedule slots.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user->isApprovedDoctor()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'schedules' => 'required|array',
            'schedules.*.day_of_week' => 'required|integer|min:0|max:6',
            'schedules.*.start_time' => 'required|date_format:H:i:s',
            'schedules.*.end_time' => 'required|date_format:H:i:s|after:schedules.*.start_time',
            'schedules.*.is_active' => 'required|boolean',
        ]);

        foreach ($request->schedules as $slot) {
            DoctorSchedule::updateOrCreate(
                [
                    'doctor_id' => $user->id,
                    'day_of_week' => $slot['day_of_week'],
                ],
                [
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                    'is_active' => $slot['is_active'],
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Schedule updated successfully.']);
    }

    /**
     * Toggle a single day's active status.
     */
    public function toggle(Request $request)
    {
        $user = Auth::user();
        if (!$user->isApprovedDoctor()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'day_of_week' => 'required|integer|min:0|max:6',
            'is_active' => 'required|boolean',
        ]);

        $schedule = DoctorSchedule::updateOrCreate(
            ['doctor_id' => $user->id, 'day_of_week' => $request->day_of_week],
            ['is_active' => $request->is_active]
        );

        return response()->json(['success' => true, 'is_active' => $schedule->is_active]);
    }
}
