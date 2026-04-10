<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;


class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email.',
            'password.required' => 'Password is required.',
        ]);

        // Normalise email casing before lookup
        $credentials['email'] = strtolower(trim($credentials['email']));
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate(); // prevents session fixation
            Log::info('User logged in', ['user_id' => Auth::id(), 'ip' => $request->ip()]);
            return redirect()->intended(route('user.dashboard'));
        }

        return back()
            ->withErrors(['email' => 'These credentials do not match our records.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Log::info('User logged out', ['user_id' => $userId, 'ip' => $request->ip()]);
        return redirect()->route('login');
    }

    public function showSignup()
    {
        return view('auth.signup');
    }





    
public function signupAjax(Request $request)
    {
        $data = $request->validate([
            'fname' => ['required', 'string', 'min:2', 'max:50'],
            'mname' => ['nullable', 'string', 'min:2', 'max:50'],
            'lname' => ['required', 'string', 'min:2', 'max:50'],
            'gender' => ['required', 'in:male,female,other,prefer_not_to_say'],
            'bday' => ['required', 'date', 'before:today'],
            'email' => ['required', 'email:rfc', 'unique:users,email', 'max:255'],
            'username' => ['required', 'min:3', 'max:20', 'alpha_dash', 'unique:users,username'],
            'password' => ['required', 'confirmed',
                Password::min(8)->mixedCase()->numbers()
            ],
        ], [

            // First name
            'fname.required' => 'First name is required.',
            'fname.min' => 'First name must be at least 2 characters.',

            // Middle name
            'mname.min' => 'Middle name must be at least 2 characters.',

            // Last name
            'lname.required' => 'Last name is required.',
            'lname.min' => 'Last name must be at least 2 characters.',

            // Gender
            'gender.required' => 'Please select your gender.',

            // Birthday
            'bday.required' => 'Birthday is required.',
            'bday.date' => 'Invalid birth date.',

            // Email
            'email.required' => 'Email is required.',
            'email.email' => 'Enter a valid email address.',
            'email.unique' => 'This email is already registered.',

            // Username
            'username.required' => 'Username is required.',
            'username.min' => 'Username must be at least 3 characters.',
            'username.max' => 'Username cannot exceed 20 characters.',
            'username.unique' => 'Username already taken.',

            // Password
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Passwords do not match.',
            'password.mixed_case' => 'Password must contain at least one uppercase and one lowercase letter.',
            'password.numbers' => 'Password must contain at least one number.',
        ]);

        try {
            $user = User::create([
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'username' => $data['username'],
                'fname' => $data['fname'],
                'mname' => $data['mname'] ?? null,
                'lname' => $data['lname'],
                'gender' => $data['gender'],
                'bday' => $data['bday'],
                'role' => 'user',
                'doctor_status' => 'none',
                'profile_photo' => 'profiles/default.png',
            ]);

            return response()->json([
                'ok' => true,
                'message' => 'Account created successfully!',
                'redirect' => route('login'),
                'user_id' => $user->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Signup failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'ok' => false,
                'message' => 'Signup failed: ' . $e->getMessage(),
            ], 500);
        }
    }

}