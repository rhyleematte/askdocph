<?php $__env->startSection('title', 'Support Groups – AskDocPH'); ?>

<?php $__env->startPush('styles'); ?>
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/groups.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
  $me = Auth::user();
?>

<main class="dash dashboard-groups-index">
  <div class="dash-body">
    <?php echo $__env->make('partials.sidebar', ['active' => 'groups'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <main class="groups-main">
      <div class="groups-header-panel">
        <div class="groups-header-left">
          <h1>Support Groups</h1>
          <p>Connect with others who understand your journey. Join supportive communities focused on healing and growth.</p>
        </div>
        <?php if($me->doctor_status === 'approved'): ?>
        <div class="groups-header-right">
          <a href="<?php echo e(route('groups.create')); ?>" class="create-group-btn" style="text-decoration: none;">
            <i data-lucide="plus"></i> Create Group
          </a>
        </div>
        <?php endif; ?>
      </div>

      <div class="groups-toolbar">
        <div class="groups-search-wrap">
          <i data-lucide="search"></i>
          <input type="text" id="groupHeaderSearch" placeholder="Search groups..." autocomplete="off">
        </div>
        <div class="groups-filter-wrap">
          <select id="groupSortSelect">
            <option value="newest" <?php echo e(($sort ?? 'newest') === 'newest' ? 'selected' : ''); ?>>Newest</option>
            <option value="oldest" <?php echo e(($sort ?? 'newest') === 'oldest' ? 'selected' : ''); ?>>Oldest</option>
            <option value="members_desc" <?php echo e(($sort ?? 'newest') === 'members_desc' ? 'selected' : ''); ?>>Highest Members</option>
            <option value="members_asc" <?php echo e(($sort ?? 'newest') === 'members_asc' ? 'selected' : ''); ?>>Lowest Members</option>
            <option value="active_desc" <?php echo e(($sort ?? 'newest') === 'active_desc' ? 'selected' : ''); ?>>Most Active</option>
            <option value="active_asc" <?php echo e(($sort ?? 'newest') === 'active_asc' ? 'selected' : ''); ?>>Least Active</option>
          </select>
        </div>
      </div>

      <div class="groups-grid">
        <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $isJoined = in_array($group->id, $myGroupIds);
        ?>
        <div class="group-card">
          <div class="group-cover" style="background-image: url('<?php echo e($group->cover_url); ?>'); background-size: cover; background-position: center;">
          </div>
          <div class="group-info">
            <h2 class="group-title"><?php echo e($group->name); ?></h2>
            <p class="group-desc"><?php echo e(Str::limit($group->description, 100)); ?></p>
            
            <div class="group-stats">
              <div class="group-stats-item">
                <i data-lucide="users"></i> <?php echo e(number_format($group->members_count)); ?> members
              </div>
              <div class="group-stats-item group-active-stat">
                <i data-lucide="trending-up"></i> <?php echo e($group->activity_level); ?>

              </div>
            </div>

            <?php if($isJoined): ?>
              <a href="<?php echo e(route('groups.show', $group->id)); ?>" class="group-btn joined">
                <i data-lucide="eye"></i> View
              </a>
            <?php else: ?>
              <button class="group-btn join" onclick="joinGroup(<?php echo e($group->id); ?>)">
                Join Group
              </button>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    </main>
  </div>
</main>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
async function joinGroup(id) {
    try {
        let res = await fetch(`/groups/${id}/join`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>', 'Accept': 'application/json' }
        });
        let data = await res.json();
        if(data.ok) location.reload();
    } catch(e) {}
}

// Simple header search filter for groups list
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('groupHeaderSearch');
    const cards = Array.from(document.querySelectorAll('.groups-grid .group-card'));

    if (!searchInput || !cards.length) return;

    const norm = (s) => (s || '').toString().trim().toLowerCase();

    function apply() {
        const q = norm(searchInput.value);
        cards.forEach(card => {
            const titleEl = card.querySelector('.group-title');
            const descEl = card.querySelector('.group-desc');
            const hay = norm((titleEl?.textContent || '') + ' ' + (descEl?.textContent || ''));
            const show = !q || hay.indexOf(q) !== -1;
            card.style.display = show ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', apply);
});

// Sort selector
document.addEventListener('DOMContentLoaded', () => {
    const sortSelect = document.getElementById('groupSortSelect');
    if (!sortSelect) return;

    sortSelect.addEventListener('change', () => {
        const url = new URL(window.location.href);
        url.searchParams.set('sort', sortSelect.value);
        window.location.href = url.toString();
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.dashboard', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\websystem\resources\views/groups/index.blade.php ENDPATH**/ ?>