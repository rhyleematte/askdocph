<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.applications.index');
        }

        $user = Auth::user();
        $rejectedApplication = null;

        if ($user->doctor_status === 'rejected') {
            $rejectedApplication = \App\Models\DoctorApplication::where('user_id', $user->id)
                ->where('status', 'rejected')
                ->latest()
                ->first();
        }

        return view('userdashboard', compact('rejectedApplication'));
    }
}
