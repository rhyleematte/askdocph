@extends('layouts.app')

@section('title', 'AskDocPH')

@section('content')
<main class="wrap">
  <section class="left">
    <div class="card">

      {{-- LOGIN VIEW --}}
      <div class="auth-view" id="loginView">
        <h1>Welcome Back</h1>
        <p class="subtitle">Continue your mental wellness journey</p>

        @if(session('success'))
          <div class="alert success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}" class="form" novalidate>
          @csrf

          <label>Email Address</label>
          <div class="input-group @error('email') has-error @enderror">
            <i data-lucide="mail"></i>
            <input
              type="email"
              name="email"
              value="{{ old('email') }}"
              placeholder="you@example.com"
              required
              autocomplete="email"
            />
          </div>
          @error('email')
            <p class="error">{{ $message }}</p>
          @enderror

          <label>Password</label>
          <div class="input-group @error('password') has-error @enderror">
            <i data-lucide="lock"></i>
            <input
              type="password"
              name="password"
              class="password-field"
              placeholder="Enter your password"
              required
              autocomplete="current-password"
            />

            <button type="button" class="toggle togglePass" aria-label="Show password">
              <i data-lucide="eye"></i>
            </button>
          </div>
          @error('password')
            <p class="error">{{ $message }}</p>
          @enderror

          <div class="row">
            <label class="check">
              <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
              Remember me
            </label>
            <a href="#" class="link">Forgot password?</a>
          </div>

          <button type="submit" class="btn primary">Sign In</button>

          <p class="switch">
            Don’t have an account?
            <button type="button" class="switch-btn" id="goRegister">Create Account</button>
          </p>
        </form>
      </div>

      {{-- REGISTER VIEW --}}
      <div class="auth-view hidden" id="registerView">
        <h1>Create Account</h1>
        <p class="subtitle">Start your mental wellness journey</p>

        <form method="POST" action="{{ route('signup.submit') }}" class="form" novalidate>
          @csrf

          <label>First Name</label>
          <div class="input-group @error('fname') has-error @enderror">
            <i data-lucide="user"></i>
            <input
              type="text"
              name="fname"
              value="{{ old('fname') }}"
              placeholder="Rhylee"
              required
              autocomplete="given-name"
            />
          </div>
          @error('fname')
            <p class="error">{{ $message }}</p>
          @enderror

          <label>Last Name</label>
          <div class="input-group @error('lname') has-error @enderror">
            <i data-lucide="user"></i>
            <input
              type="text"
              name="lname"
              value="{{ old('lname') }}"
              placeholder="Matte"
              required
              autocomplete="family-name"
            />
          </div>
          @error('lname')
            <p class="error">{{ $message }}</p>
          @enderror

          <label>Email Address</label>
          <div class="input-group @error('reg_email') has-error @enderror">
            <i data-lucide="mail"></i>
            <input
              type="email"
              name="reg_email"
              value="{{ old('reg_email') }}"
              placeholder="you@example.com"
              required
              autocomplete="email"
            />
          </div>
          @error('reg_email')
            <p class="error">{{ $message }}</p>
          @enderror

          <label>Password</label>
          <div class="input-group @error('reg_password') has-error @enderror">
            <i data-lucide="lock"></i>
            <input
              type="password"
              name="reg_password"
              class="password-field"
              placeholder="Create a password"
              required
              autocomplete="new-password"
            />
            <button type="button" class="toggle togglePass" aria-label="Show password">
              <i data-lucide="eye"></i>
            </button>
          </div>
          @error('reg_password')
            <p class="error">{{ $message }}</p>
          @enderror

          <label>Confirm Password</label>
          <div class="input-group @error('reg_password_confirmation') has-error @enderror">
            <i data-lucide="shield-check"></i>
            <input
              type="password"
              name="reg_password_confirmation"
              class="password-field"
              placeholder="Confirm your password"
              required
              autocomplete="new-password"
            />
            <button type="button" class="toggle togglePass" aria-label="Show password">
              <i data-lucide="eye"></i>
            </button>
          </div>
          @error('reg_password_confirmation')
            <p class="error">{{ $message }}</p>
          @enderror

          <button type="submit" class="btn primary">Create Account</button>

          <p class="switch">
            Already have an account?
            <button type="button" class="switch-btn" id="goLogin">Sign In</button>
          </p>
        </form>
      </div>

    </div>
  </section>

  <section class="right">
    <div class="right-content">
      <h2>Your mental health journey<br>starts here</h2>

      <div class="feature">
        <div class="badge"><i data-lucide="users"></i></div>
        <div>
          <h3>Connect with Professionals</h3>
          <p>Access licensed psychiatrists and mental health experts.</p>
        </div>
      </div>

      <div class="feature">
        <div class="badge"><i data-lucide="heart-handshake"></i></div>
        <div>
          <h3>Join Our Community</h3>
          <p>Share experiences and find support.</p>
        </div>
      </div>

      <div class="feature">
        <div class="badge"><i data-lucide="shield-check"></i></div>
        <div>
          <h3>Safe & Confidential</h3>
          <p>Your privacy and security are our priority.</p>
        </div>
      </div>
    </div>

    <div style="margin-top: auto; text-align: center; padding-top: 40px;">
        <a href="{{ route('doctor.apply') }}" style="color: rgba(255, 255, 255, 0.8); text-decoration: underline; font-size: 14px; transition: color 0.3s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255, 255, 255, 0.8)'">Apply for medical staff privileges</a>
    </div>
  </section>
</main>
@endsection
