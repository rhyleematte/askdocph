<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminAuthController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.applications.index');
        }
        return view('admin.auth.login');
    }

    /**
     * Handle admin login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();

            // Store Admin Session Security Metadata
            $request->session()->put('admin_last_activity', time());
            $request->session()->put('admin_ip', $request->ip());
            $request->session()->put('admin_user_agent', $request->userAgent());

            return redirect()->route('admin.applications.index');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show the admin signup form.
     */
    public function showSignup()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.applications.index');
        }
        return view('admin.auth.signup');
    }

    /**
     * Handle admin signup request.
     */
    public function signup(Request $request)
    {
        $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'mname' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8|confirmed',
            'gender' => 'required|in:male,female,other',
            'bday' => 'required|date|before:today',
        ]);

        $admin = Admin::create([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'mname' => $request->mname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
            'bday' => $request->bday,
        ]);

        Auth::guard('admin')->login($admin);

        // Store Admin Session Security Metadata
        $request->session()->put('admin_last_activity', time());
        $request->session()->put('admin_ip', $request->ip());
        $request->session()->put('admin_user_agent', $request->userAgent());

        return redirect()->route('admin.applications.index');
    }

    /**
     * Log the admin out.
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
