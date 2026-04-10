<?php
  $header_user = Auth::user() ?? Auth::guard('admin')->user();
  if ($header_user) {
      $header_avatarUrl = $header_user->avatar_url;
      if (Auth::guard('admin')->check() && !str_starts_with($header_avatarUrl, 'http')) {
          $header_avatarUrl = $header_avatarUrl ? asset('storage/' . $header_avatarUrl) : asset('assets/img/default.png');
      }
      $header_fullName  = $header_user->full_name ?: ($header_user->name ?? 'User');
      $header_shortName = $header_user->short_name ?: $header_fullName;
      $header_username  = $header_user->username ?? ($header_user->email ?? 'admin');
      $isDoctor         = method_exists($header_user, 'isApprovedDoctor') ? $header_user->isApprovedDoctor() : false;
  } else {
      $header_avatarUrl = asset('assets/img/default.png');
      $header_fullName  = 'Guest';
      $header_shortName = 'Guest';
      $header_username  = 'guest';
      $isDoctor         = false;
  }
?>

<header class="dash-topbar">
  <div class="brand">
    <a href="<?php echo e(route('user.dashboard')); ?>">
      <img src="<?php echo e(asset('assets/img/AskDocPH.png')); ?>" class="logo" alt="AskDocPH">
    </a>
  </div>

  <div class="dash-search">
    <i data-lucide="search"></i>
    <input type="text" id="globalSearchInput" placeholder="Search for support, resources, or people..." />
    <div class="search-dropdown" id="globalSearchDropdown"></div>
  </div>

  <div class="dash-actions">
    <button class="icon-btn schedule-toggle" type="button" aria-label="Schedule" id="headerScheduleBtn">
      <i data-lucide="calendar"></i>
    </button>
    <button class="icon-btn messenger-toggle" type="button" aria-label="Messages">
      <i data-lucide="message-circle"></i>
    </button>
    <button class="icon-btn" type="button" aria-label="Notifications" id="headerNotifBtn">
      <i data-lucide="bell"></i>
      <span class="dot" id="headerNotifDot" style="display:none;"></span>
    </button>

    
    <div class="avatar-dropdown">
      <button class="avatar-btn" type="button" id="profileToggle"
              aria-label="Profile" aria-haspopup="true" aria-expanded="false">
        <img src="<?php echo e($header_avatarUrl); ?>" alt="User" />
        <div class="avatar-meta">
          <div class="avatar-name">
            <?php echo e($header_shortName); ?>

            <?php if($isDoctor): ?>
              <i data-lucide="badge-check" class="doctor-badge" title="Approved Doctor"></i>
            <?php endif; ?>
          </div>
          <div class="avatar-username">
            <?php echo e('@'.$header_username); ?>

          </div>
        </div>
        <i data-lucide="chevron-down" class="dropdown-icon"></i>
      </button>

      <div class="dropdown-menu" id="profileDropdown" aria-labelledby="profileToggle">
        <a href="<?php echo e(route('profile.show', Auth::id())); ?>" class="dropdown-profile-link">
          <div class="dropdown-profile">
            <div class="dropdown-avatar"><img src="<?php echo e($header_avatarUrl); ?>" alt="User" /></div>
            <div class="dropdown-info">
              <div class="profile-fullname">
                <?php echo e($header_fullName); ?>

                <?php if($isDoctor): ?>
                  <i data-lucide="badge-check" class="doctor-badge" title="Approved Doctor"></i>
                <?php endif; ?>
              </div>
              <div class="profile-username"><?php echo e('@'.$header_username); ?></div>
            </div>
          </div>
        </a>
        <hr class="dropdown-divider">
        <button type="button" class="dropdown-item" id="themeToggleBtn">
          <i data-lucide="moon"></i><span>Dark mode</span>
        </button>
        <?php if(Auth::guard('admin')->check()): ?>
        <hr class="dropdown-divider">
        <a href="<?php echo e(route('admin.applications.index')); ?>" class="dropdown-item" style="text-decoration: none; color: inherit;">
          <i data-lucide="users"></i><span>Applications</span>
        </a>

        <a href="<?php echo e(route('admin.professional-titles.index')); ?>" class="dropdown-item" style="text-decoration: none; color: inherit;">
          <i data-lucide="briefcase"></i><span>Professional Titles</span>
        </a>
        <?php endif; ?>
        <hr class="dropdown-divider">
        <form method="POST" action="<?php echo e(route('logout')); ?>" class="logout-form">
          <?php echo csrf_field(); ?>
          <button type="submit" class="dropdown-logout">
            <i data-lucide="log-out"></i><span>Logout</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</header>
<?php /**PATH C:\websystem\resources\views/partials/header.blade.php ENDPATH**/ ?>