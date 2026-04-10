@php
  $user = Auth::user();
  $avatarUrl = $user->avatar_url;
  $fullName = $user->full_name ?: ($user->name ?? 'User');
  $shortName = $user->short_name ?: $fullName;
  $username = $user->username ?? 'username';
@endphp

<header class="dash-topbar">
  <div class="brand">
    <img src="{{ asset('assets/img/AskDocPH.png') }}" class="logo" alt="AskDocPH">
  </div>

  <div class="dash-search">
    <i data-lucide="search"></i>
    <input type="text" placeholder="Search for support, resources, or people..." />
  </div>

  <div class="dash-actions">
    <a href="{{ route('user.dashboard') }}" class="icon-btn" title="Dashboard">
      <i data-lucide="home"></i>
    </a>
    <button class="icon-btn" type="button" aria-label="Notifications">
      <i data-lucide="bell"></i>
      <span class="dot"></span>
    </button>

    <div class="avatar-dropdown">
      <button class="avatar-btn" type="button" id="profileToggle">
        <img src="{{ $avatarUrl }}" alt="User" />
        <div class="avatar-meta">
          <div class="avatar-name">{{ $shortName }}</div>
          <div class="avatar-username">{{ '@'.$username }}</div>
        </div>
        <i data-lucide="chevron-down" class="dropdown-icon"></i>
      </button>

      <div class="dropdown-menu" id="profileDropdown">
        <a href="{{ route('profile.show', $user->id) }}" class="dropdown-profile-link">
          <div class="dropdown-profile">
            <div class="dropdown-avatar"><img src="{{ $avatarUrl }}" alt="User" /></div>
            <div class="dropdown-info">
              <div class="profile-fullname">{{ $fullName }}</div>
              <div class="profile-username">{{ '@'.$username }}</div>
            </div>
          </div>
        </a>
        <hr class="dropdown-divider">
        <button type="button" class="dropdown-item" id="themeToggleBtn">
          <i data-lucide="moon"></i><span>Dark mode</span>
        </button>
        <hr class="dropdown-divider">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="dropdown-logout">
            <i data-lucide="log-out"></i><span>Logout</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</header>
