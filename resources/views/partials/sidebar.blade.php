@php
    $sidebar_user = Auth::user();
    $sidebar_me = $sidebar_user; // compatibility
    $active = $active ?? 'feed';
@endphp

<aside class="dash-left">
    <div class="panel nav-panel">
        <a class="nav-item {{ $active === 'feed' ? 'active' : '' }}" href="{{ route('user.dashboard') }}">
            <i data-lucide="home"></i><span>Feed</span>
        </a>
        <a class="nav-item {{ $active === 'groups' ? 'active' : '' }}" href="{{ route('groups.index') }}">
            <i data-lucide="users"></i><span>Support Groups</span>
        </a>
        <a class="nav-item {{ $active === 'resources' ? 'active' : '' }}" href="{{ route('resources.index') }}">
            <i data-lucide="book-open"></i><span>Resources</span>
        </a>
        <a class="nav-item {{ $active === 'profile' ? 'active' : '' }}"
            href="{{ route('profile.show', $sidebar_user->id) }}">
            <i data-lucide="user"></i><span>My Profile</span>
        </a>

        @if($sidebar_user->role !== 'doctor' && $sidebar_user->doctor_status !== 'approved' && $sidebar_user->doctor_status !== 'none' && $sidebar_user->doctor_status !== null)
            <a class="nav-item {{ $active === 'application' ? 'active' : '' }}"
                href="{{ route('profile.show', $sidebar_user->id) }}?tab=application">
                <i data-lucide="stethoscope"></i><span>Apply as Doctor</span>
            </a>
        @endif
    </div>

    @include('partials.daily_affirmation_panel')

    <div class="panel mini-panel danger">
        <div class="mini-title"><i data-lucide="life-buoy"></i><span>Crisis Support</span></div>
        <p class="mini-sub">If you're in crisis, help is available 24/7</p>
        <button class="danger-btn" type="button" id="getHelpBtn">Get Help Now</button>
    </div>

</aside>