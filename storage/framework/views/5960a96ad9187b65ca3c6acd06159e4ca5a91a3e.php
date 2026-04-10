<?php $__env->startSection('title', 'Post - AskDocPH'); ?>

<?php $__env->startPush('styles'); ?>
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/post.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
  $me = Auth::user();
  $backUrl = ($post->group_id && isset($group))
    ? route('groups.show', $group->id)
    : route('profile.show', $post->user_id);

  $backLabel = ($post->group_id && isset($group))
    ? 'Back to Group'
    : 'Back to Profile';

  $contextLabel = ($post->group_id && isset($group))
    ? ('Group: ' . $group->name)
    : ('Posted by @' . $post->user->username);
?>

<main class="dash single-post-page">

  <div class="dash-body single-post-body">
    <section class="dash-main single-post-main">
      <div class="panel single-post-header">
        <a class="back-link" href="<?php echo e($backUrl); ?>">
          <i data-lucide="arrow-left"></i>
          <span><?php echo e($backLabel); ?></span>
        </a>

        <?php if($post->group_id && $group): ?>
          <a class="context-link" href="<?php echo e(route('groups.show', $group->id)); ?>"><?php echo e($contextLabel); ?></a>
        <?php else: ?>
          <a class="context-link" href="<?php echo e(route('profile.show', $post->user_id)); ?>"><?php echo e($contextLabel); ?></a>
        <?php endif; ?>
      </div>

      <?php if($canView): ?>
        <?php echo $__env->make('profile._post', ['post' => $post, 'me' => $me, 'group' => $group], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <?php else: ?>
        <div class="panel empty-state">
          <i data-lucide="lock"></i>
          <p>You must join this group to view this post.</p>
          <?php if($group): ?>
            <a class="btn primary" href="<?php echo e(route('groups.show', $group->id)); ?>">Go to Group</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </section>
  </div>
</main>

<div class="toast" id="toast"></div>

<script>
  window.AUTH_USER_ID = <?php echo e($me ? $me->id : 'null'); ?>;
  window.ROUTES = {
    toggleLike: function(id){ return '/profile/posts/' + id + '/like'; },
    toggleSave: function(id){ return '/profile/posts/' + id + '/save'; },
    storeComment: function(id){ return '/profile/posts/' + id + '/comments'; },
    destroyComment: function(id){ return '/profile/comments/' + id; },
    updatePost: function(id){ return '/profile/posts/' + id; },
    destroyPost: function(id){ return '/profile/posts/' + id; },
    profileNetwork: function(id){ return '/api/profile/' + id + '/network'; },
  };
</script>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
  <script src="<?php echo e(asset('assets/js/profile.js?v=' . time())); ?>" defer></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.dashboard', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\websystem\resources\views/posts/show.blade.php ENDPATH**/ ?>