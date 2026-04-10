<div class="comment-item" id="comment-<?php echo e($comment->id); ?>">
  <div class="avatar sm">
    <img src="<?php echo e($comment->user->avatar_url); ?>" alt="<?php echo e($comment->user->full_name); ?>">
  </div>
  <div class="comment-bubble">
    <div class="comment-meta">
      <span class="comment-author"><?php echo e($comment->user->full_name); ?></span>
      <span class="comment-time"><?php echo e($comment->created_at->diffForHumans()); ?></span>
      <?php if($me && $comment->user_id === $me->id): ?>
      <button class="comment-delete-btn" type="button" data-comment-id="<?php echo e($comment->id); ?>" title="Delete">
        <i data-lucide="x"></i>
      </button>
      <?php endif; ?>
    </div>
    <p class="comment-text"><?php echo preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank" rel="noopener noreferrer" class="post-link" style="color:var(--brand);text-decoration:underline;">$1</a>', htmlspecialchars($comment->comment_text)); ?></p>
    <?php if(auth()->guard()->check()): ?>
    <button class="reply-toggle-btn" type="button" data-comment-id="<?php echo e($comment->id); ?>" data-post-id="<?php echo e($post->id); ?>">
      Reply
    </button>
    <?php endif; ?>

    
    <?php if(auth()->guard()->check()): ?>
    <div class="reply-composer hidden" id="reply-composer-<?php echo e($comment->id); ?>">
      <input type="text" class="comment-input reply-input" placeholder="Write a reply…"
             data-post-id="<?php echo e($post->id); ?>" data-parent-id="<?php echo e($comment->id); ?>">
      <button class="comment-send-btn reply-send-btn" type="button"
              data-post-id="<?php echo e($post->id); ?>" data-parent-id="<?php echo e($comment->id); ?>">
        <i data-lucide="send"></i>
      </button>
    </div>
    <?php endif; ?>

    
    <?php if($comment->replies && $comment->replies->isNotEmpty()): ?>
    <div class="replies-list" id="replies-<?php echo e($comment->id); ?>">
      <?php $__currentLoopData = $comment->replies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="comment-item reply-item" id="comment-<?php echo e($reply->id); ?>">
          <div class="avatar sm">
            <img src="<?php echo e($reply->user->avatar_url); ?>" alt="<?php echo e($reply->user->full_name); ?>">
          </div>
          <div class="comment-bubble">
            <div class="comment-meta">
              <span class="comment-author"><?php echo e($reply->user->full_name); ?></span>
              <span class="comment-time"><?php echo e($reply->created_at->diffForHumans()); ?></span>
              <?php if($me && $reply->user_id === $me->id): ?>
              <button class="comment-delete-btn" type="button" data-comment-id="<?php echo e($reply->id); ?>" title="Delete">
                <i data-lucide="x"></i>
              </button>
              <?php endif; ?>
            </div>
            <p class="comment-text"><?php echo preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank" rel="noopener noreferrer" class="post-link" style="color:var(--brand);text-decoration:underline;">$1</a>', htmlspecialchars($reply->comment_text)); ?></p>
            <?php if(auth()->guard()->check()): ?>
            <button class="reply-toggle-btn" type="button" data-comment-id="<?php echo e($comment->id); ?>" data-post-id="<?php echo e($post->id); ?>" data-reply-to="<?php echo e($reply->user->username); ?>">
              Reply
            </button>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php else: ?>
    <div class="replies-list" id="replies-<?php echo e($comment->id); ?>"></div>
    <?php endif; ?>
  </div>
</div>
<?php /**PATH C:\websystem\resources\views/profile/_comment.blade.php ENDPATH**/ ?>