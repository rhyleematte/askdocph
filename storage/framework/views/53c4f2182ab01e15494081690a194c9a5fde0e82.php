<?php
    $sidebar_user = Auth::user();
    $sidebar_me = $sidebar_user; // compatibility
    $active = $active ?? 'feed';
?>

<aside class="dash-left">
    <div class="panel nav-panel">
        <a class="nav-item <?php echo e($active === 'feed' ? 'active' : ''); ?>" href="<?php echo e(route('user.dashboard')); ?>">
            <i data-lucide="home"></i><span>Feed</span>
        </a>
        <a class="nav-item <?php echo e($active === 'groups' ? 'active' : ''); ?>" href="<?php echo e(route('groups.index')); ?>">
            <i data-lucide="users"></i><span>Support Groups</span>
        </a>
        <a class="nav-item <?php echo e($active === 'resources' ? 'active' : ''); ?>" href="<?php echo e(route('resources.index')); ?>">
            <i data-lucide="book-open"></i><span>Resources</span>
        </a>
        <a class="nav-item <?php echo e($active === 'profile' ? 'active' : ''); ?>"
            href="<?php echo e(route('profile.show', $sidebar_user->id)); ?>">
            <i data-lucide="user"></i><span>My Profile</span>
        </a>

        <?php if($sidebar_user->role !== 'doctor' && $sidebar_user->doctor_status !== 'approved' && $sidebar_user->doctor_status !== 'none' && $sidebar_user->doctor_status !== null): ?>
            <a class="nav-item <?php echo e($active === 'application' ? 'active' : ''); ?>"
                href="<?php echo e(route('profile.show', $sidebar_user->id)); ?>?tab=application">
                <i data-lucide="stethoscope"></i><span>Apply as Doctor</span>
            </a>
        <?php endif; ?>
    </div>

    <?php echo $__env->make('partials.daily_affirmation_panel', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="panel mini-panel danger">
        <div class="mini-title"><i data-lucide="life-buoy"></i><span>Crisis Support</span></div>
        <p class="mini-sub">If you're in crisis, help is available 24/7</p>
        <button class="danger-btn" type="button" id="getHelpBtn">Get Help Now</button>
    </div>

</aside><?php /**PATH C:\websystem\resources\views/partials/sidebar.blade.php ENDPATH**/ ?>