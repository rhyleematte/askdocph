<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentInvitation;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AppointmentController extends Controller
{
    public function show($id)
    {
        $appointment = Appointment::withTrashed()->findOrFail($id);
        $user = Auth::user();

        // Check if user is creator or invited
        $invitation = AppointmentInvitation::where('appointment_id', $appointment->id)
            ->where('user_id', $user->id)
            ->first();

        if ($appointment->creator_id !== $user->id && !$invitation) {
            abort(403, 'You do not have permission to view this appointment.');
        }

        return view('appointments.show', [
            'appointment' => $appointment,
            'invitation' => $invitation,
            'isCreator' => $appointment->creator_id === $user->id,
            'isDeleted' => $appointment->trashed()
        ]);
    }

    public function create()
    {
        return view('appointments.create');
    }

    public function getEvents(Request $request)
    {
        $user = Auth::user();
        try {
            // Replace spaces with + in case of URL decoding issues (common for +08:00)
            $startStr = str_replace(' ', '+', $request->query('start', ''));
            $endStr = str_replace(' ', '+', $request->query('end', ''));

            $start = $startStr ? Carbon::parse($startStr) : Carbon::now()->startOfMonth();
            $end = $endStr ? Carbon::parse($endStr) : Carbon::now()->endOfMonth();
        } catch (\Exception $e) {
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
        }

        // Appointments created by the user
        $owned = Appointment::where('creator_id', $user->id)
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->with(['invitations.user'])
            ->get()
            ->map(function ($a) {
                $invitees = $a->invitations->map(function($i) {
                    return [
                        'id' => $i->user->id,
                        'name' => $i->user->full_name,
                        'avatar' => $i->user->avatar_url
                    ];
                });

                return [
                    'id' => $a->id,
                    'title' => $a->subject,
                    'start' => $a->start_at ? $a->start_at->toIso8601String() : null,
                    'end' => $a->end_at ? $a->end_at->toIso8601String() : null,
                    'description' => $a->description,
                    'location' => $a->location,
                    'color' => '#4e73df', // Primary color for owned
                    'extendedProps' => [
                        'type' => 'owned',
                        'location' => $a->location,
                        'creator' => 'You',
                        'invited_users' => $invitees,
                    ]
                ];
            })->filter(fn($ev) => !is_null($ev['start']));

        // Appointments THE USER HAS ACCEPTED
        $invited = AppointmentInvitation::where('user_id', $user->id)
            ->where('status', 'accepted')
            ->whereHas('appointment', function ($q) use ($start, $end) {
                $q->where('start_at', '<', $end)
                    ->where('end_at', '>', $start);
            })
            ->with(['appointment.creator', 'appointment.invitations.user'])
            ->get()
            ->map(function ($inv) {
                $statusColor = [
                    'pending' => '#f6c23e', // Warning/Yellow
                    'accepted' => '#1cc88a', // Success/Green
                    'declined' => '#e74a3b', // Danger/Red
                ];

                if (!$inv->appointment) return null;

                return [
                    'id' => 'inv-' . $inv->id,
                    'appointment_id' => $inv->appointment_id,
                    'title' => $inv->appointment->subject,
                    'start' => $inv->appointment->start_at ? $inv->appointment->start_at->toIso8601String() : null,
                    'end' => $inv->appointment->end_at ? $inv->appointment->end_at->toIso8601String() : null,
                    'description' => $inv->appointment->description,
                    'location' => $inv->appointment->location,
                    'color' => $statusColor[$inv->status] ?? '#858796',
                    'extendedProps' => [
                        'type' => 'invitation',
                        'status' => $inv->status,
                        'location' => $inv->appointment->location,
                        'creator' => $inv->appointment->creator ? $inv->appointment->creator->full_name : 'Unknown',
                        'invited_users' => $inv->appointment->invitations->map(function($i) {
                            return [
                                'id' => $i->user->id,
                                'name' => $i->user->full_name,
                                'avatar' => $i->user->avatar_url
                            ];
                        }),
                    ]
                ];
            })->filter(fn($ev) => !is_null($ev));

        $events = $owned->concat($invited);

        // Fetch holidays for the start and end years (limit to 2 years max for speed)
        $years = array_unique([$start->year, $end->year]);
        foreach ($years as $year) {
            $holidays = $this->getHolidays($year);
            $events = $events->concat($holidays);
        }

        return response()->json($events);
    }

    private function getHolidays($year)
    {
        // Cache for 30 days (rarely changes)
        return Cache::remember("ph_holidays_{$year}", 2592000, function () use ($year) {
            try {
                $response = Http::timeout(3)->get("https://date.nager.at/api/v3/PublicHolidays/{$year}/PH");
                if ($response->successful()) {
                    return collect($response->json())->map(function ($h) {
                        return [
                            'id' => 'holiday-' . $h['date'] . '-' . str_replace(' ', '-', $h['name']),
                            'title' => $h['name'],
                            'start' => $h['date'],
                            'allDay' => true,
                            'display' => 'block',
                            'classNames' => ['fc-event-holiday'],
                            'editable' => false,
                            'color' => '#1cc88a', // Default green for holidays
                            'extendedProps' => [
                                'type' => 'holiday',
                                'creator' => 'Republic of the Philippines',
                                'description' => $h['localName'] !== $h['name'] ? $h['localName'] : 'Public Holiday',
                            ]
                        ];
                    });
                }
            } catch (\Exception $e) {
                // Silently fail if API is down
            }
            return collect();
        });
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'start_at' => 'required|date|after_or_equal:now',
            'end_at' => 'required|date|after:start_at',
            'reminder_minutes' => 'nullable|integer',
            'auto_send_brief' => 'nullable|boolean',
            'invited_user_ids' => 'nullable|array',
            'invited_user_ids.*' => 'exists:users,id',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        $user = Auth::user();

        // Handle Image Upload
        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('assets/img/appointments'), $filename);
            $coverPath = 'assets/img/appointments/' . $filename;
        }

        // Check for conflicts for the creator
        if ($this->hasConflict($user->id, $request->start_at, $request->end_at)) {
            return response()->json(['ok' => false, 'message' => 'You already have an appointment during this time.'], 422);
        }

        $appointment = DB::transaction(function () use ($request, $user, $coverPath) {
            $appointment = Appointment::create([
                'creator_id' => $user->id,
                'subject' => $request->subject,
                'location' => $request->location,
                'description' => $request->description,
                'start_at' => $request->start_at,
                'end_at' => $request->end_at,
                'reminder_minutes' => $request->reminder_minutes ?? 15,
                'auto_send_brief' => $request->auto_send_brief ?? false,
                'cover_image' => $coverPath,
            ]);

            if ($request->invited_user_ids) {
                foreach ($request->invited_user_ids as $invitedId) {
                    if ($invitedId == $user->id) continue;

                    AppointmentInvitation::create([
                        'appointment_id' => $appointment->id,
                        'user_id' => $invitedId,
                        'status' => 'pending',
                    ]);

                    $invitedUser = User::find($invitedId);
                    NotificationService::create($invitedUser, $user, 'appointment_invite', [
                        'message' => $user->full_name . ' invited you to an appointment: ' . $appointment->subject,
                        'url' => route('appointments.show', $appointment->id),
                        'appointment_id' => $appointment->id,
                    ]);
                }
            }

            return $appointment;
        });
 
        return response()->json([
            'ok' => true, 
            'message' => 'Appointment created successfully.',
            'id' => $appointment->id
        ]);
    }

    public function respond(Request $request, AppointmentInvitation $invitation)
    {
        $request->validate([
            'status' => 'required|in:accepted,declined',
        ]);

        abort_if($invitation->user_id !== Auth::id(), 403);

        if ($request->status === 'accepted') {
            // Check for conflicts before accepting
            if ($this->hasConflict(Auth::id(), $invitation->appointment->start_at, $invitation->appointment->end_at)) {
                return response()->json(['ok' => false, 'message' => 'You have a conflicting appointment at this time.'], 422);
            }
        }

        $invitation->update(['status' => $request->status]);

        // Notify creator
        $appointment = $invitation->appointment;
        $creator = $appointment->creator;
        $user = Auth::user();

        NotificationService::create($creator, $user, 'appointment_response', [
            'message' => $user->full_name . ' ' . $request->status . ' your appointment invitation: ' . $appointment->subject,
            'url' => route('appointments.show', $appointment->id),
            'appointment_id' => $appointment->id,
            'status' => $request->status,
        ]);

        return response()->json(['ok' => true, 'message' => 'Invitation ' . $request->status . '.']);
    }

    public function destroy(Appointment $appointment)
    {
        abort_if($appointment->creator_id !== Auth::id(), 403);
        
        $user = Auth::user();
        $participants = $appointment->participants;

        // Notify all participants about cancellation
        foreach ($participants as $participant) {
            NotificationService::create($participant, $user, 'appointment_canceled', [
                'message' => 'CANCELED: ' . $user->full_name . ' has canceled the appointment: ' . $appointment->subject,
                'url' => route('appointments.show', $appointment->id),
                'appointment_id' => $appointment->id,
            ]);
        }

        $appointment->delete();
        return response()->json(['ok' => true, 'message' => 'Appointment canceled and participants notified.']);
    }

    public function update(Request $request, Appointment $appointment)
    {
        abort_if($appointment->creator_id !== Auth::id(), 403);

        $request->validate([
            'subject'     => 'required|string|max:255',
            'location'    => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'start_at'    => 'required|date',
            'end_at'      => 'required|date|after:start_at',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        $user = Auth::user();

        // Conflict check — exclude this appointment itself
        $start = \Carbon\Carbon::parse($request->start_at);
        $end   = \Carbon\Carbon::parse($request->end_at);

        $conflict = Appointment::where('creator_id', $user->id)
            ->where('id', '!=', $appointment->id)
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->first();

        if ($conflict) {
            return response()->json([
                'ok' => false,
                'message' => 'You already have another appointment during this time: "' . $conflict->subject . '".',
            ], 422);
        }

        // Handle cover image replacement
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('assets/img/appointments'), $filename);
            $appointment->cover_image = 'assets/img/appointments/' . $filename;
        }

        $appointment->subject     = $request->subject;
        $appointment->location    = $request->location;
        $appointment->description = $request->description;
        $appointment->start_at    = $request->start_at;
        $appointment->end_at      = $request->end_at;
        $appointment->save();

        // Notify participants of the update
        foreach ($appointment->participants as $participant) {
            NotificationService::create($participant, $user, 'appointment_updated', [
                'message' => $user->full_name . ' updated the appointment: ' . $appointment->subject,
                'url'     => route('appointments.show', $appointment->id),
                'appointment_id' => $appointment->id,
            ]);
        }

        return response()->json([
            'ok'      => true,
            'message' => 'Appointment updated successfully.',
        ]);
    }

    public function checkConflicts(Request $request)
    {
        $request->validate([
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $conflicts = [];
        $userIds = $request->input('user_ids', []);
        $userIds[] = Auth::id(); // Always check for self

        foreach (array_unique($userIds) as $uid) {
            $conflictApt = $this->hasConflict($uid, $request->start_at, $request->end_at);
            if ($conflictApt) {
                $u = User::find($uid);
                $conflicts[] = [
                    'id' => $u->id,
                    'name' => $u->id === Auth::id() ? 'You' : $u->full_name,
                    'subject' => $conflictApt->subject,
                    'start' => $conflictApt->start_at->toIso8601String(),
                    'end' => $conflictApt->end_at->toIso8601String(),
                ];
            }
        }

        return response()->json([
            'ok' => true,
            'has_conflict' => !empty($conflicts),
            'conflicts' => $conflicts,
        ]);
    }

    private function hasConflict($userId, $start, $end)
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        // Check owned appointments
        $conflict = Appointment::where('creator_id', $userId)
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->first();

        if ($conflict) return $conflict;

        // Check accepted invitations
        $invitationConflict = AppointmentInvitation::where('user_id', $userId)
            ->where('status', 'accepted')
            ->whereHas('appointment', function ($q) use ($start, $end) {
                $q->where('start_at', '<', $end)
                    ->where('end_at', '>', $start);
            })
            ->with('appointment')
            ->first();

        return $invitationConflict ? $invitationConflict->appointment : null;
    }
}
