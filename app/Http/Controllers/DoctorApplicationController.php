<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DoctorRequirement;
use App\Models\DoctorApplication;
use App\Models\DoctorApplicationDocument;
use App\Models\User;
use App\Models\AdminNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use App\Models\ProfessionalTitle;

class DoctorApplicationController extends Controller
{
    public function create()
    {
        $user = auth()->user();

        if ($user) {
            if ($user->role === 'doctor' || $user->doctor_status === 'approved') {
                return redirect()->route('user.dashboard')->with('success', 'You are already a doctor.');
            }

            if (($user->doctor_status === 'none' || $user->doctor_status === null) && $user->doctor_status !== 'applying') {
                return redirect()->route('user.dashboard')->with('error', 'Only doctor applicants can access the application portal.');
            }

            $application = DoctorApplication::where('user_id', $user->id)->first();

            // If pending, show pending view
            if ($user->doctor_status === 'pending' || ($application && $application->status === 'pending')) {
                return view('doctor.pending', compact('application'));
            }

            // If rejected, show rejected feedback view
            if ($user->doctor_status === 'rejected') {
                return view('doctor.rejected', compact('application'));
            }
        }

        $requirements = DoctorRequirement::all();
        $application = $user ?DoctorApplication::where('user_id', $user->id)->first() : null;
        $professional_titles = ProfessionalTitle::orderBy('name')->get();

        return view('doctor.apply', compact('requirements', 'application', 'user', 'professional_titles'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        // 1. Initial Validation Setup
        $requirements = DoctorRequirement::all();
        $rules = [];
        $messages = [];

        // Validation for guest (new user)
        if (!$user) {
            $rules = [
                'fname' => ['required', 'string', 'min:2', 'max:50'],
                'mname' => ['nullable', 'string', 'min:2', 'max:50'],
                'lname' => ['required', 'string', 'min:2', 'max:50'],
                'gender' => ['required', 'in:male,female,other,prefer_not_say'],
                'bday' => ['required', 'date', 'before:today'],
                'email' => ['required', 'email:rfc,dns', 'unique:users,email', 'max:255'],
                'username' => ['required', 'min:3', 'max:20', 'alpha_dash', 'unique:users,username'],
                'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
                'professional_titles' => ['required', 'string', 'exists:professional_titles,name'],
                'biometric_consent' => ['required', 'accepted'],
                'liveness_verified' => ['required', 'in:1'],
                'face_match_score' => ['required', 'numeric', 'min:0', 'max:100'],
                'biometric_payload' => ['required', 'string']
            ];

            $messages = [
                'fname.required' => 'First name is required.',
                'lname.required' => 'Last name is required.',
                'email.unique' => 'This email is already registered.',
                'username.unique' => 'Username already taken.',
                'password.confirmed' => 'Passwords do not match.',
                'liveness_verified.in' => 'Liveliness and Face Match verification is incomplete.',
            ];
        }
        else {
            if ($user->doctor_status === 'pending' || $user->doctor_status === 'approved') {
                return redirect()->route('user.dashboard')->with('error', 'Invalid application state.');
            }
            // Additional rules if user is already logged in
            $rules = [
                'professional_titles' => ['required', 'string', 'exists:professional_titles,name'],
                'biometric_consent' => ['required', 'accepted'],
                'liveness_verified' => ['required', 'in:1'],
                'face_match_score' => ['required', 'numeric', 'min:0', 'max:100'],
                'biometric_payload' => ['required', 'string']
            ];
            $messages = [
                'liveness_verified.in' => 'Liveliness and Face Match verification is incomplete.',
            ];
        }

        // 2. Add Requirements to Validation
        foreach ($requirements as $req) {
            $validators = 'file|max:51200'; // increased size for video uploads
            if (stripos($req->name, 'video') !== false) {
                $validators .= '|mimetypes:video/mp4,video/x-m4v,video/*';
            }
            else {
                $validators .= '|mimes:pdf,jpg,jpeg,png';
            }

            if ($req->is_required) {
                $rules['req_' . $req->id] = 'required|' . $validators;
                $messages['req_' . $req->id . '.required'] = $req->name . ' is required.';
            }
            else {
                $rules['req_' . $req->id] = 'nullable|' . $validators;
            }
        }

        $request->validate($rules, $messages);

        // 3. Create User if Guest
        if (!$user) {
            $user = User::create([
                'email' => strtolower(trim($request->email)),
                'password' => Hash::make($request->password),
                'username' => $request->username,
                'fname' => $request->fname,
                'mname' => $request->mname,
                'lname' => $request->lname,
                'gender' => $request->gender,
                'bday' => $request->bday,
                'role' => 'user',
                'doctor_status' => 'pending', // Important: set to pending
                'profile_photo' => 'profiles/default.png',
            ]);

            // Log the new user in
            Auth::login($user);
            $request->session()->regenerate();
        }
        else {
            // Update existing user status
            $user->update(['doctor_status' => 'pending']);
            // delete previous rejected application if any
            DoctorApplication::where('user_id', $user->id)->delete();
        }

        // 4. Create Doctor Application with biometrics
        // Hash the base64 payload to simulate secure reference index
        $referenceHash = hash('sha256', $request->input('biometric_payload') . uniqid());

        $application = DoctorApplication::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'submitted_at' => now(),
            'professional_titles' => $request->input('professional_titles'),
            'biometric_consent' => $request->boolean('biometric_consent'),
            'liveness_verified' => true,
            'face_match_score' => $request->input('face_match_score'),
            'biometric_verified_at' => now(),
            'biometric_reference_hash' => $referenceHash,
        ]);

        // 5. Store Requirements
        foreach ($requirements as $req) {
            $key = 'req_' . $req->id;
            if ($request->hasFile($key)) {
                $path = $request->file($key)->store('doctor_documents', 'public');

                DoctorApplicationDocument::create([
                    'doctor_application_id' => $application->id,
                    'doctor_requirement_id' => $req->id,
                    'document_type' => 'file',
                    'file_path' => $path,
                    'status' => 'submitted',
                ]);
            }
        }

        // 6. Notify all admins of new doctor application
        AdminNotification::createForAll('doctor_application', [
            'application_id'  => $application->id,
            'applicant_name'  => trim($user->fname . ' ' . $user->lname),
            'applicant_email' => $user->email,
            'url'             => url('/admin/applications/' . $application->id),
            'submitted_at'    => now()->toDateTimeString(),
        ]);

        return redirect()->back()->with('success', 'Your application has been submitted successfully and is pending approval. You are now logged in.');
    }
    public function reapply(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->doctor_status !== 'rejected') {
            return redirect()->route('user.dashboard');
        }

        // Transition user to 'applying' state so they can access the form again
        $user->update(['doctor_status' => 'applying']);

        return redirect()->route('doctor.apply')->with('success', 'You can now submit a new petition to re-apply.');
    }
}
