<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <title><?php echo $__env->yieldContent('title', 'Admin - AskDocPH'); ?></title>
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap">

  <!-- Base + Dashboard CSS -->
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/dashboard.css')); ?>">
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/messenger.css')); ?>">

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>

  <!-- Page-specific styles -->
  <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body>

  <?php
    $layout_admin = Auth::guard('admin')->user();
    $layout_admin_avatar = $layout_admin->avatar_url ? asset('storage/'.$layout_admin->avatar_url) : asset('assets/img/default.png');
    $layout_admin_name = addslashes($layout_admin->short_name ?: $layout_admin->email);
  ?>
  <script>
    window.MY_ID = <?php echo e($layout_admin->id); ?>;
    window.MY_AVATAR = "<?php echo e($layout_admin_avatar); ?>";
    window.MY_NAME = "<?php echo e($layout_admin_name); ?>";
  </script>
  <?php echo $__env->make('partials.admin_header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php echo $__env->yieldContent('content'); ?>

<!-- Inject Admin Messenger Drawer -->
<?php echo $__env->make('partials.admin_messenger', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<!-- Admin Dashboard JS -->
<script src="<?php echo e(asset('assets/js/admin-ui.js')); ?>?v=<?php echo e(filemtime(public_path('assets/js/admin-ui.js'))); ?>" defer></script>
<script src="<?php echo e(asset('assets/js/admin-spa.js')); ?>?v=<?php echo e(filemtime(public_path('assets/js/admin-spa.js'))); ?>" defer></script>
<script src="<?php echo e(asset('assets/js/admin-messenger.js')); ?>?v=<?php echo e(filemtime(public_path('assets/js/admin-messenger.js'))); ?>" defer></script>
<script src="<?php echo e(asset('assets/js/admin-notifications.js')); ?>?v=<?php echo e(filemtime(public_path('assets/js/admin-notifications.js'))); ?>" defer></script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\websystem\resources\views/layouts/admin.blade.php ENDPATH**/ ?>