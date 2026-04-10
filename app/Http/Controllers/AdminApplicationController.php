<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DoctorApplication;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;

class AdminApplicationController extends Controller
{
    // For testing/demonstration purposes, this uses an open route. In production, protect this using an Admin Auth Middleware.
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'all');
        $search = $request->query('search');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        $query = DoctorApplication::with(['user', 'documents.requirement']);

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('fname', 'like', '%' . $search . '%')
                    ->orWhere('lname', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Date Filtering logic
        if ($fromDate && $toDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay()
            ]);
        }
        elseif ($fromDate) {
            $query->where('created_at', '>=', Carbon::parse($fromDate)->startOfDay());
        }
        elseif ($toDate) {
            $query->where('created_at', '<=', Carbon::parse($toDate)->endOfDay());
        }

        // Filtering & Sorting rules
        if ($tab === 'pending') {
            $query->where('status', 'pending')->orderBy('created_at', 'asc'); // Oldest first
        }
        elseif ($tab === 'approved') {
            $query->where('status', 'approved')->orderBy('updated_at', 'desc'); // Newest first
        }
        elseif ($tab === 'rejected') {
            $query->where('status', 'rejected')->orderBy('updated_at', 'desc'); // Newest first
        }
        else {
            // "All" Tab: Show Pending first (oldest first), then Approved/Rejected (newest first).
            // We use a custom order by raw string to prioritize 'pending' status.
            $query->orderByRaw("
                CASE 
                    WHEN status = 'pending' THEN 1 
                    ELSE 2 
                END ASC
            ")
                ->orderByRaw("
                CASE 
                    WHEN status = 'pending' THEN created_at 
                END ASC
            ")
                ->orderByRaw("
                CASE 
                    WHEN status != 'pending' THEN updated_at 
                END DESC
            ");
        }

        $applications = $query->get();

        $counts = [
            'all' => \App\Models\DoctorApplication::count(),
            'pending' => \App\Models\DoctorApplication::where('status', 'pending')->count(),
            'approved' => \App\Models\DoctorApplication::where('status', 'approved')->count(),
            'rejected' => \App\Models\DoctorApplication::where('status', 'rejected')->count(),
        ];

        return view('admin.applications.index', compact('applications', 'tab', 'search', 'fromDate', 'toDate', 'counts'));
    }

    public function show(Request $request, $id)
    {
        $application = DoctorApplication::with(['user', 'documents.requirement'])->findOrFail($id);
        $requirements = \App\Models\DoctorRequirement::all();

        $tab = $request->query('tab', 'all');
        $search = $request->query('search');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        return view('admin.applications.show', compact('application', 'requirements', 'tab', 'search', 'fromDate', 'toDate'));
    }

    public function approve(Request $request, $id)
    {
        $application = DoctorApplication::findOrFail($id);

        $application->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'admin_notes' => $request->admin_notes,
            // 'reviewed_by_admin_id' => admin() id
        ]);

        $application->user->update([
            'role' => 'doctor',
            'doctor_status' => 'approved'
        ]);

        // Accept all documents for simplicity if approved
        foreach ($application->documents as $doc) {
            $doc->update(['status' => 'accepted']);
        }

        $user = $application->user;
        if ($user) {
            NotificationService::create($user, null, 'doctor_approved', [
                'message' => 'Your doctor application has been approved.',
                'url' => route('profile.show', $user->id) . '?tab=application',
                'application_id' => $application->id,
            ]);
        }

        return redirect()->route('admin.applications.index')->with('success', 'Application approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $application = DoctorApplication::findOrFail($id);

        $application->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        $application->user->update([
            'doctor_status' => 'rejected'
        ]);

        // Reject documents
        foreach ($application->documents as $doc) {
            $doc->update(['status' => 'rejected']);
        }

        return redirect()->route('admin.applications.index')->with('success', 'Application rejected.');
    }
}
