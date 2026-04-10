<?php
  $liked     = $me ? $post->isLikedBy($me->id) : false;
  $isSaved   = $me ? $post->isSavedBy($me->id) : false;
  $isOwner   = $me && $post->user_id === $me->id;
  $isGroupCreator = isset($group) && $me && $group->creator_id === $me->id;
  $canManage = $isOwner || $isGroupCreator;
  $canEdit   = $isOwner;
?>

<article class="panel post" data-post-id="<?php echo e($post->id); ?>">
  
  <div class="post-head">
    <div class="avatar md">
      <img src="<?php echo e($post->user->avatar_url); ?>" alt="<?php echo e($post->user->full_name); ?>">
    </div>
    <?php
      $isDoctor = $post->user && $post->user->role === 'doctor' && $post->user->doctor_status === 'approved';
      $verifiedBadge = $isDoctor ? '<i data-lucide="badge-check" class="doctor-badge" title="Verified Doctor"></i>' : '';
      $profTitleHtml = ($isDoctor && $post->user->professional_titles && trim($post->user->professional_titles)) 
        ? '<div class="prof-title">' . e(trim($post->user->professional_titles)) . '</div>' 
        : '';
    ?>
    <div class="post-meta">
      <div class="post-name-row">
        <span class="post-name"><?php echo e($post->user->full_name); ?></span>
        <span class="post-handle"><?php echo e('@' . $post->user->username); ?></span>
        <?php echo $verifiedBadge; ?>

      </div>
      <?php echo $profTitleHtml; ?>

      <div class="post-sub"><?php echo e($post->created_at->diffForHumans()); ?></div>
    </div>
    <?php if($canManage): ?>
    <div class="post-menu-wrap">
      <button class="icon-btn post-menu-btn" type="button" title="Options">
        <i data-lucide="more-horizontal"></i>
      </button>
      <div class="post-menu hidden">
        <?php if($canEdit): ?>
        <button class="post-menu-item edit-post-btn" type="button"
            data-post-id="<?php echo e($post->id); ?>"
            data-text="<?php echo e($post->text_content ?? ''); ?>"
            data-media="<?php echo e(json_encode($post->media->map(function($m) { return ['id' => $m->id, 'url' => asset('storage/' . $m->path), 'media_type' => $m->media_type]; }))); ?>">
          <i data-lucide="pencil"></i> Edit
        </button>
        <?php endif; ?>
        <button class="post-menu-item delete-post-btn danger" type="button"
            data-post-id="<?php echo e($post->id); ?>">
          <i data-lucide="trash-2"></i> Delete
        </button>
      </div>
    </div>
    <?php endif; ?>
  </div>

  
  <?php if($post->text_content): ?>
  <div class="post-body post-text-content js-collapsible"><?php echo preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank" rel="noopener noreferrer" class="post-link" style="color:var(--brand);text-decoration:underline;">$1</a>', htmlspecialchars($post->text_content)); ?></div>
  <?php endif; ?>

  <?php if(!empty($post->hashtags_array)): ?>
    <div class="post-tags">
      <?php $__currentLoopData = $post->hashtags_array; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <span class="tag">#<?php echo e($tag); ?></span>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  <?php endif; ?>

  
  <?php if($post->resource): ?>
    <a href="<?php echo e(route('resources.show', $post->resource->id)); ?>" class="post-resource-card" style="display:flex; gap:12px; border:1px solid var(--border); border-radius:14px; padding:12px; text-decoration:none; color:inherit; margin-bottom:12px;">
      <div class="res-mini-thumb" style="width:64px; height:64px; border-radius:12px; overflow:hidden; flex-shrink:0; background:var(--hover); border:1px solid var(--border);">
        <img src="<?php echo e($post->resource->thumbnail_url); ?>" alt="<?php echo e($post->resource->title); ?>" style="width:100%; height:100%; object-fit:cover;">
      </div>
      <div class="res-mini-info" style="min-width:0; display:flex; flex-direction:column; gap:4px;">
        <div class="res-mini-type" style="font-size:11px; font-weight:800; color:var(--muted); text-transform:uppercase;"><?php echo e($post->resource->type); ?></div>
        <div class="res-mini-title" style="font-weight:900; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo e($post->resource->title); ?></div>
        <div class="res-mini-desc" style="font-size:13px; color:var(--muted); overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;"><?php echo e($post->resource->description); ?></div>
      </div>
    </a>
  <?php endif; ?>

<?php
/* Group share block removed as requested */
?>

  
  <?php if($post->sharedPost): ?>
    <div class="shared-post-card">
      <div class="post-head">
        <div class="avatar md">
          <img src="<?php echo e($post->sharedPost->user->avatar_url); ?>" alt="<?php echo e($post->sharedPost->user->full_name); ?>">
        </div>
        <?php
          $sharedUser = $post->sharedPost->user;
        ?>
        <div class="post-meta">
          <?php
            $isSharedDoctor = $sharedUser && $sharedUser->doctor_status === 'approved' && (!$sharedUser->role || $sharedUser->role === 'doctor');
            $spVerifiedBadge = $isSharedDoctor ? '<i data-lucide="badge-check" class="doctor-badge" title="Verified Doctor"></i>' : '';
            $spProfTitleHtml = ($isSharedDoctor && $sharedUser->professional_titles && trim($sharedUser->professional_titles))
              ? '<div class="prof-title">' . e(trim($sharedUser->professional_titles)) . '</div>'
              : '';
          ?>
          <div class="post-name-row">
            <span class="post-name"><?php echo e($sharedUser->full_name); ?></span>
            <span class="post-handle"><?php echo e('@' . $sharedUser->username); ?></span>
            <?php echo $spVerifiedBadge; ?>

          </div>
          <?php echo $spProfTitleHtml; ?>

          <div class="post-sub"><?php echo e($post->sharedPost->created_at->diffForHumans()); ?></div>
        </div>
      </div>
      <?php if($post->sharedPost->text_content): ?>
        <div class="post-body js-collapsible"><?php echo preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank" rel="noopener noreferrer" class="post-link" style="color:var(--brand);text-decoration:underline;">$1</a>', htmlspecialchars($post->sharedPost->text_content)); ?></div>
      <?php endif; ?>
      <?php if($post->sharedPost->resource): ?>
        <a href="<?php echo e(route('resources.show', $post->sharedPost->resource->id)); ?>" class="post-resource-card" style="display:flex; gap:12px; border:1px solid var(--border); border-radius:14px; padding:12px; text-decoration:none; color:inherit; margin-top:10px;">
          <div class="res-mini-thumb" style="width:64px; height:64px; border-radius:12px; overflow:hidden; flex-shrink:0; background:var(--hover); border:1px solid var(--border);">
            <img src="<?php echo e($post->sharedPost->resource->thumbnail_url); ?>" alt="<?php echo e($post->sharedPost->resource->title); ?>" style="width:100%; height:100%; object-fit:cover;">
          </div>
          <div class="res-mini-info" style="min-width:0; display:flex; flex-direction:column; gap:4px;">
            <div class="res-mini-type" style="font-size:11px; font-weight:800; color:var(--muted); text-transform:uppercase;"><?php echo e($post->sharedPost->resource->type); ?></div>
            <div class="res-mini-title" style="font-weight:900; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo e($post->sharedPost->resource->title); ?></div>
            <div class="res-mini-desc" style="font-size:13px; color:var(--muted); overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;"><?php echo e($post->sharedPost->resource->description); ?></div>
          </div>
        </a>
      <?php endif; ?>
      <?php if($post->sharedPost->media && $post->sharedPost->media->isNotEmpty()): ?>
        <div class="post-media-grid shared-post-media-grid media-count-<?php echo e(min($post->sharedPost->media->count(), 4)); ?>"
             data-media="<?php echo e(json_encode($post->sharedPost->media->map(function($m) { return ['id' => $m->id, 'url' => asset('storage/' . $m->path), 'media_type' => $m->media_type]; })->values())); ?>">
          <?php $__currentLoopData = $post->sharedPost->media->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($m->media_type === 'video'): ?>
              <video src="<?php echo e(asset('storage/' . $m->path)); ?>" controls class="post-media-item"></video>
            <?php else: ?>
              <img src="<?php echo e(asset('storage/' . $m->path)); ?>" alt="Shared media" class="post-media-item">
            <?php endif; ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          <?php if($post->sharedPost->media->count() > 4): ?>
            <div class="media-more">+<?php echo e($post->sharedPost->media->count() - 4); ?></div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  
  <?php if($post->media->isNotEmpty()): ?>
  <div class="post-media-grid media-count-<?php echo e(min($post->media->count(), 4)); ?>"
       data-media="<?php echo e(json_encode($post->media->map(function($m) { return ['id' => $m->id, 'url' => asset('storage/' . $m->path), 'media_type' => $m->media_type]; }))); ?>">
    <?php $__currentLoopData = $post->media->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php if($media->media_type === 'video'): ?>
        <video src="<?php echo e(asset('storage/' . $media->path)); ?>" controls class="post-media-item"></video>
      <?php else: ?>
        <img src="<?php echo e(asset('storage/' . $media->path)); ?>" alt="Post image" class="post-media-item">
      <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php if($post->media->count() > 4): ?>
      <div class="media-more">+<?php echo e($post->media->count() - 4); ?></div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  
  <div class="post-actions">
    <button class="post-btn like-btn <?php echo e($liked ? 'liked' : ''); ?>" type="button"
        data-post-id="<?php echo e($post->id); ?>">
      <i data-lucide="<?php echo e($liked ? 'heart' : 'heart'); ?>" class="like-icon"></i>
      <span class="like-count"><?php echo e($post->likes->count()); ?></span>
    </button>

    <button class="post-btn comment-toggle-btn" type="button" data-post-id="<?php echo e($post->id); ?>">
      <i data-lucide="message-square"></i>
      <span class="comment-count"><?php echo e($post->allComments()->count()); ?></span>
    </button>

    <button class="post-btn save-btn <?php echo e($isSaved ? 'saved' : ''); ?> end" type="button" title="Save" data-post-id="<?php echo e($post->id); ?>" data-saved="<?php echo e($isSaved ? '1' : '0'); ?>">
      <i data-lucide="bookmark"></i>
    </button>

    <button class="post-btn js-share-post" type="button" title="Share"
            data-post-id="<?php echo e($post->id); ?>"
            data-preview="<?php echo e($post->text_content ? \Illuminate\Support\Str::limit($post->text_content, 80) : 'a post'); ?>">
      <i data-lucide="share-2"></i>
    </button>
  </div>

  
  <div class="comments-section hidden" id="comments-<?php echo e($post->id); ?>">
    
    <?php if(auth()->guard()->check()): ?>
    <div class="comment-composer">
      <div class="avatar sm">
        <img src="<?php echo e(Auth::user()->avatar_url); ?>" alt="You">
      </div>
      <div class="comment-input-wrap">
        <input type="text"
               class="comment-input"
               placeholder="Write a comment…"
               data-post-id="<?php echo e($post->id); ?>">
        <button class="comment-send-btn" type="button" data-post-id="<?php echo e($post->id); ?>">
          <i data-lucide="send"></i>
        </button>
      </div>
    </div>
    <?php endif; ?>

    
    <div class="comments-list" id="comments-list-<?php echo e($post->id); ?>">
      <?php $__currentLoopData = $post->comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php echo $__env->make('profile._comment', ['comment' => $comment, 'me' => $me, 'post' => $post], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  </div>
</article>
<?php /**PATH C:\websystem\resources\views/profile/_post.blade.php ENDPATH**/ ?>