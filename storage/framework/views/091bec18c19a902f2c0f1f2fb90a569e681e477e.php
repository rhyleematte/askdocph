<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $__env->yieldContent('title', 'AskDocPH'); ?></title>

  <!-- CSS -->
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/auth.css')); ?>">

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>

  
  <?php echo $__env->yieldPushContent('styles'); ?>



</head>
<body>

<header class="topbar">
  <div class="brand">
    <a href="<?php echo e(auth()->check() ? route('user.dashboard') : route('home')); ?>">
      <img src="<?php echo e(asset('assets/img/AskDocPH.png')); ?>" class="logo" alt="AskDocPH">
    </a>
  </div>

  <nav class="nav" style="display: flex; align-items: center; gap: 20px;">
    <?php if(auth()->guard()->check()): ?>
      <?php
        $user = auth()->user();
        $avatarUrl = $user->avatar_url ?? asset('assets/img/default.png');
        $shortName = $user->short_name ?? $user->fname;
      ?>
      <a href="<?php echo e(route('user.dashboard')); ?>" style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: var(--text); font-weight: 600; transition: opacity 0.2s;" onmouseover="this.style.opacity=0.8" onmouseout="this.style.opacity=1">
        <img src="<?php echo e($avatarUrl); ?>" alt="User" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border);">
        <span><?php echo e($shortName); ?></span>
      </a>
      <form method="POST" action="<?php echo e(route('logout')); ?>" style="margin: 0;">
        <?php echo csrf_field(); ?>
        <button type="submit" class="btn" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 8px 16px; border-radius: 8px; font-weight: 600; transition: background 0.2s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.2)'" onmouseout="this.style.background='rgba(239, 68, 68, 0.1)'">
          Logout
        </button>
      </form>
    <?php else: ?>
      <a href="<?php echo e(route('about')); ?>" class="nav-link">About</a>
      <a href="<?php echo e(route('signup')); ?>" class="btn signup-btn">Sign Up</a>
    <?php endif; ?>
  </nav>
</header>

<?php echo $__env->yieldContent('content'); ?>

<!-- JS -->
<script src="<?php echo e(asset('assets/js/auth.js')); ?>" defer></script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\websystem\resources\views/layouts/app.blade.php ENDPATH**/ ?>