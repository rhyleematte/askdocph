@extends('layouts.app')

@section('title', 'Admin Signup - AskDocPH')

@section('content')
<div class="auth-wrap">
  <div class="auth-card" style="max-width: 500px;">
    <div class="auth-header" style="text-align: center; margin-bottom: 2rem;">
      <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Create Admin Account</h1>
      <p style="color: var(--text-muted); font-size: 0.875rem;">Register to manage the system</p>
    </div>

    @if($errors->any())
      <div class="alert-error" style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--error); color: var(--error); padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem;">
        @foreach($errors->all() as $error)
          <div>{{ $error }}</div>
        @endforeach
      </div>
    @endif

    <form action="{{ route('admin.signup.submit') }}" method="POST">
      @csrf

      <div style="display: flex; gap: 1rem;">
        <div class="form-group" style="margin-bottom: 1.25rem; flex: 1;">
          <label for="fname" style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">First Name</label>
          <input type="text" id="fname" name="fname" class="form-control" value="{{ old('fname') }}" required autofocus style="width: 100%; padding: 0.75rem 1rem; border-radius: 6px; border: 1px solid var(--border); background: var(--bg); color: var(--text); font-size: 1rem; transition: border-color 0.2s;">
        </div>
        <div class="form-group" style="margin-bottom: 1.25rem; flex: 1;">
          <label for="lname" style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Last Name</label>
          <input type="text" id="lname" name="lname" class="form-control" value="{{ old('lname') }}" required style="width: 100%; padding: 0.75rem 1rem; border-radius: 6px; border: 1px solid var(--border); background: var(--bg); color: var(--text); font-size: 1rem; transition: border-color 0.2s;">
        </div>
      </div>

      <div class="form-group" style="margin-bottom: 1.25rem;">
        <label for="mname" style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Middle Name (Optional)</label>
        <input type="text" id="mname" name="mname" class="form-control" value="{{ old('mname') }}" style="width: 100%; padding: 0.75rem 1rem; border-radius: 6px; border: 1px solid var(--border); background: var(--bg); color: var(--text); font-size: 1rem; transition: border-color 0.2s;">
      </div>

      <div class="form-group" style="margin-bottom: 1.25rem;">
        <label for="email" style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Email Address</label>
        <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required style="width: 100%; padding: 0.75rem 1rem; border-radius: 6px; border: 1px solid var(--border); background: var(--bg); color: var(--text); font-size: 1rem; transition: border-color 0.2s;">
      </div>

      <div style="display: flex; gap: 1rem;">
        <div class="form-group" style="margin-bottom: 1.25rem; flex: 1;">
          <label for="gender" style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Gender</label>
          <select id="gender" name="gender" required style="width: 100%; padding: 0.75rem 1rem; border-radius: 6px; border: 1px solid var(--border); background: var(--bg); color: var(--text); font-size: 1rem; transition: border-color 0.2s; appearance: none;">
            <option value="">Select...</option>
            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
            <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
          </select>
        </div>
        <div class="form-group" style="margin-bottom: 1.25rem; flex: 1;">
          <label for="bday" style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Date of Birth</label>
          <input type="date" id="bday" name="bday" class="form-control" value="{{ old('bday') }}" required style="width: 100%; padding: 0.75rem 1rem; border-radius: 6px; border: 1px solid var(--border); background: var(--bg); color: var(--text); font-size: 1rem; transition: border-color 0.2s;">
        </div>
      </div>

      <div style="display: flex; gap: 1rem;">
        <div class="form-group" style="margin-bottom: 1.25rem; flex: 1;">
          <label for="password" style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Password</label>
          <input type="password" id="password" name="password" class="form-control" required style="width: 100%; padding: 0.75rem 1rem; border-radius: 6px; border: 1px solid var(--border); background: var(--bg); color: var(--text); font-size: 1rem; transition: border-color 0.2s;">
        </div>
        <div class="form-group" style="margin-bottom: 1.25rem; flex: 1;">
          <label for="password_confirmation" style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Confirm Password</label>
          <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required style="width: 100%; padding: 0.75rem 1rem; border-radius: 6px; border: 1px solid var(--border); background: var(--bg); color: var(--text); font-size: 1rem; transition: border-color 0.2s;">
        </div>
      </div>

      <button type="submit" class="btn" style="width: 100%; padding: 0.75rem; border: none; border-radius: 6px; background: var(--primary); color: white; font-size: 1rem; font-weight: 500; cursor: pointer; transition: background 0.2s;">Create Account</button>
    </form>

    <div class="auth-footer" style="margin-top: 1.5rem; text-align: center; font-size: 0.875rem; color: var(--text-muted);">
      Already have an account? <a href="{{ route('admin.login') }}" style="color: var(--primary); text-decoration: none;">Sign in</a>
    </div>
  </div>
</div>
@endsection
