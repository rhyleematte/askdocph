@php
  $admin_header_user = Auth::guard('admin')->user();
  $admin_header_name  = $admin_header_user ? ($admin_header_user->short_name ?: $admin_header_user->email) : 'Admin';
  $admin_header_email = $admin_header_user ? $admin_header_user->email : '';
  $admin_avatar = $admin_header_user && $admin_header_user->avatar_url
      ? asset('storage/' . $admin_header_user->avatar_url)
      : asset('assets/img/default.png');

  $admin_notif_count = $admin_header_user
      ? \App\Models\AdminNotification::unreadCountFor($admin_header_user->id)
      : 0;

  $admin_msg_count = $admin_header_user
      ? \App\Models\AdminMessage::where('to_admin_id', $admin_header_user->id)->whereNull('read_at')->count()
      : 0;
@endphp

<header class="dash-topbar">
  <div class="brand">
    <a href="{{ route('admin.applications.index') }}">
      <img src="{{ asset('assets/img/AskDocPH.png') }}" class="logo" alt="AskDocPH">
      <span style="font-size: 0.65rem; font-weight: 800; background: var(--teal, #0c8f98); color: white; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; margin-left: 8px; vertical-align: middle;">Admin</span>
    </a>
  </div>

  <div class="dash-search" style="visibility: hidden;"></div>

  <div class="dash-actions">
    {{-- Messages (Drawer Toggle) --}}
    <button class="icon-btn admin-messenger-toggle" title="Admin Messages">
      <i data-lucide="message-square"></i>
      @if($admin_msg_count > 0)
        <span class="dot">{{ $admin_msg_count > 9 ? '9+' : $admin_msg_count }}</span>
      @endif
    </button>

    {{-- Notifications --}}
    <button aria-label="Admin Notifications" class="icon-btn" id="adminNotifBtn">
      <i data-lucide="bell"></i>
      {{-- Badge will be managed by admin-notifications.js --}}
      <span class="dot notif-badge" style="display: {{ $admin_notif_count > 0 ? 'inline-flex' : 'none' }};">{{ $admin_notif_count > 9 ? '9+' : $admin_notif_count }}</span>
    </button>

    {{-- Profile dropdown --}}
    <div class="avatar-dropdown">
      <button class="avatar-btn" type="button" id="profileToggle"
              aria-label="Profile" aria-haspopup="true" aria-expanded="false">
        <img src="{{ $admin_avatar }}" alt="Admin" />
        <div class="avatar-meta">
          <div class="avatar-name">{{ $admin_header_name }}</div>
          <div class="avatar-username">Administrator</div>
        </div>
        <i data-lucide="chevron-down" class="dropdown-icon"></i>
      </button>

      <div class="dropdown-menu" id="profileDropdown" aria-labelledby="profileToggle">
        <a href="{{ route('admin.applications.index') }}" class="dropdown-item" style="text-decoration: none; color: inherit;">
          <i data-lucide="users"></i><span>Applications</span>
        </a>
        <a href="{{ route('admin.professional-titles.index') }}" class="dropdown-item" style="text-decoration: none; color: inherit;">
          <i data-lucide="briefcase"></i><span>Professional Titles</span>
        </a>
        <a href="{{ route('admin.daily-affirmations.index') }}" class="dropdown-item" style="text-decoration: none; color: inherit;">
          <i data-lucide="sparkles"></i><span>Daily Affirmations</span>
        </a>
        <hr class="dropdown-divider">
        <button type="button" class="dropdown-item" id="themeToggleBtn">
          <i data-lucide="moon"></i><span>Dark mode</span>
        </button>
        <hr class="dropdown-divider">
        <form method="POST" action="{{ route('admin.logout') }}" class="logout-form">
          @csrf
          <button type="submit" class="dropdown-logout" style="color: #ef4444;">
            <i data-lucide="log-out"></i><span>Logout</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</header>
