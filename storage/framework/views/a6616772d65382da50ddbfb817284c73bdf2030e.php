<?php $__env->startSection('title', 'Resources – AskDocPH'); ?>

<?php $__env->startPush('styles'); ?>
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/resources.css')); ?>?v=<?php echo e(time()); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
  <?php
    $me = Auth::user();
  ?>

  <main class="dash">
    <div class="dash-body">
      <?php echo $__env->make('partials.sidebar', ['active' => 'resources'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

      <main class="res-main">
        <div class="res-header-panel">
          <div class="res-header-left">
            <h1>Mental Health Resources</h1>
            <p>Find professional tools, articles, and media to support your wellness journey.</p>
          </div>
          <?php if(Auth::check() && Auth::user()->can('create', 'App\Models\Resource')): ?>
            <div class="res-header-right">
              <a href="<?php echo e(route('resources.create')); ?>" class="create-res-btn">
                <i data-lucide="plus"></i> Create Resource
              </a>
            </div>
          <?php endif; ?>
        </div>

        
        <div class="res-filterbar panel">
          <div class="res-search">
            <i data-lucide="search"></i>
            <input id="resSearchInput" type="text" placeholder="Search resources..." autocomplete="off">
          </div>
          <div class="res-filters" id="resFilters">
            <button class="chip-btn active" type="button" data-filter="all">All</button>
            <button class="chip-btn" type="button" data-filter="Article">Articles</button>
            <button class="chip-btn" type="button" data-filter="Audio">Audio</button>
            <button class="chip-btn" type="button" data-filter="Video">Videos</button>
            <button class="chip-btn" type="button" data-filter="Workbook">Workbooks</button>
          </div>
        </div>

        <div class="res-grid">
          <?php if($resources->count() > 0): ?>
            <?php $__currentLoopData = $resources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $res): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <div class="res-card" data-type="<?php echo e($res->type); ?>" data-title="<?php echo e(strtolower($res->title ?? '')); ?>"
                data-desc="<?php echo e(strtolower($res->description ?? '')); ?>" data-tags="<?php echo e(strtolower($res->hashtags ?? '')); ?>">
                <div class="res-card-thumb">
                  <img src="<?php echo e($res->thumbnail_url); ?>" alt="<?php echo e($res->title); ?>">
                  <span class="res-card-type"><?php echo e($res->type); ?></span>
                </div>
                <div class="res-card-content">
                  <h3 class="res-card-title"><?php echo e(\Illuminate\Support\Str::limit($res->title, 50)); ?></h3>
                  <p class="res-card-desc"><?php echo e(\Illuminate\Support\Str::limit($res->description, 100)); ?></p>

                  <?php
                    $tags = $res->hashtags_array ?? [];
                    $tags = array_values(array_filter(array_map(function ($t) {
                      $t = ltrim(trim((string) $t), '#');
                      return $t === '' ? null : $t;
                    }, $tags)));
                  ?>
                  <?php if(count($tags)): ?>
                    <div class="res-card-tags" aria-label="Hashtags">
                      <?php $__currentLoopData = array_slice($tags, 0, 4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="res-card-tag">#<?php echo e($tag); ?></span>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                      <?php if(count($tags) > 4): ?>
                        <span class="res-card-tag res-card-tag-more">+<?php echo e(count($tags) - 4); ?></span>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>

                  <div class="res-card-footer">
                    <div class="res-card-meta">
                      <div class="res-meta-item">
                        <i data-lucide="user"></i>
                        <span><?php echo e($res->user->short_name ?: $res->user->full_name); ?></span>
                      </div>
                      <?php if($res->duration_meta): ?>
                        <div class="res-meta-item">
                          <i data-lucide="clock"></i>
                          <span><?php echo e($res->duration_meta); ?></span>
                        </div>
                      <?php endif; ?>
                    </div>
                    <div>
                      <?php if(auth()->guard()->check()): ?>
                        <?php
                          $isJoined = in_array($res->id, $joinedResourceIds ?? []);
                        ?>
                        <a href="<?php echo e(route('resources.show', $res->id)); ?>" class="res-card-btn">
                          <?php echo e($isJoined ? 'Joined' : 'View More'); ?>

                        </a>
                      <?php endif; ?>

                      <?php if(auth()->guard()->guest()): ?>
                        <a href="<?php echo e(route('resources.show', $res->id)); ?>" class="res-card-btn">View More</a>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          <?php else: ?>
            <div class="res-empty" style="grid-column: 1 / -1;">
              <i data-lucide="book-copy"></i>
              <p>No resources found yet. Check back soon!</p>
            </div>
          <?php endif; ?>
        </div>

        <div class="res-empty hidden" id="resNoResults" style="grid-column: 1 / -1; margin-top: 16px;">
          <i data-lucide="search-x"></i>
          <p>No matching resources.</p>
        </div>
      </main>
    </div>
  </main>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
  <script>
        document.addEventListener('DOMContentLoaded', function (    ) {
        var search = document.getElementById('resSearchInput');
        var filters = document.getElementById('resFilters');
        var cards = Array.from(document.querySelectorAll('.res-grid .res-card'));
        var noResults = document.getElementById('resNoResults');

        function norm(s) { return (s || '').toString().trim().toLowerCase(); }
        var state = { q: '', type: 'all' };

        function apply() {
          var shown = 0;
          cards.forEach(function (card) {
            var type = card.dataset.type || '';
            var hay = (card.dataset.title || '') + ' ' + (card.dataset.desc || '') + ' ' + (card.dataset.tags || '') + ' ' + norm(type);
            var okType = (state.type === 'all') || (type === state.type);
            var okQ = !state.q || hay.indexOf(state.q) !== -1;
            var show = okType && okQ;
            card.style.display = show ? '' : 'none';
            if (show) shown++;
          });

          if (noResults) noResults.classList.toggle('hidden', shown !== 0 || cards.length === 0);
          if (window.lucide) lucide.createIcons();
        }

        if (search) {
          search.addEventListener('input', function () {
            state.q = norm(search.value);
            apply();
          });
        }

        if (filters) {
          filters.addEventListener('click', function (e) {
            var btn = e.target.closest('button[data-filter]');
            if (!btn) return;
            state.type = btn.dataset.filter || 'all';
            filters.querySelectorAll('button[data-filter]').forEach(function (b) {
              b.classList.toggle('active', b === btn);
            });
            apply();
          });
        }
      });
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.dashboard', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\websystem\resources\views/resources/index.blade.php ENDPATH**/ ?>