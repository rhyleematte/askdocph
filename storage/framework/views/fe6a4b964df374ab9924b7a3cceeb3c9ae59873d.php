<?php $__env->startSection('title', $group->name . ' – AskDocPH'); ?>
<?php $__env->startPush('styles'); ?>
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/groups.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
  $me_shortName = $me ? ($me->short_name ?: $me->full_name) : 'User';
  $avatarUrl = $me ? $me->avatar_url : asset('assets/img/default.png');
?>


<script>
window.DASH_ROUTES = {
  feed:          "", // No dynamic feed fetching on load here; posts are loaded server-side
  storePost:     "<?php echo e(route('profile.posts.store')); ?>",
  toggleLike:    function(id){ return "/profile/posts/" + id + "/like"; },
  storeComment:  function(id){ return "/profile/posts/" + id + "/comments"; },
  destroyComment:function(id){ return "/profile/comments/" + id; },
  destroyPost:   function(id){ return "/profile/posts/" + id; },
  updatePost:    function(id){ return "/profile/posts/" + id; },
};
window.MY_PROFILE_URL = "<?php echo e(route('profile.show', $me->id ?? 0)); ?>";
</script>

<main class="dash">
  <div class="dash-body">
    <div class="dash-left">
      <?php echo $__env->make('partials.sidebar', ['active' => 'groups'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

      
      <div class="panel mini-panel" style="margin-top: 8px;">
        <div class="mini-title">
          <i data-lucide="book-open"></i>
          <span>Group Guidelines</span>
        </div>
        <?php if($group->guidelines): ?>
        <ul class="group-guidelines-list" style="padding-left: 18px; margin-top: 12px;">
          <?php $__currentLoopData = explode("\n", $group->guidelines); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(trim($rule)): ?>
              <li style="font-size:13px; margin-bottom:10px; color: var(--muted); line-height: 1.5;">
                <div class="guideline-text"><?php echo e(trim($rule)); ?></div>
                <button type="button" class="read-more-btn guideline-toggle" style="display:none; margin-top: 4px;">Read More</button>
              </li>
            <?php endif; ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
        <?php else: ?>
        <ul class="group-guidelines-list" style="padding-left: 18px; margin-top: 12px;">
          <li style="font-size:14px; margin-bottom:12px; color: var(--muted);">Be respectful and supportive.</li>
          <li style="font-size:14px; margin-bottom:12px; color: var(--muted);">Maintain confidentiality.</li>
          <li style="font-size:14px; margin-bottom:12px; color: var(--muted);">No medical advice allowed.</li>
        </ul>
        <?php endif; ?>
      </div>
    </div>

    <main class="groups-main">
      <div class="groups-header-panel">
        <div class="groups-header-left">
          <?php if(request('from') === 'profile' && request('profile_id')): ?>
            <a href="<?php echo e(route('profile.show', request('profile_id'))); ?>?tab=groups" class="chip-btn">
              <i data-lucide="arrow-left"></i> Back to Profile
            </a>
          <?php else: ?>
            <a href="<?php echo e(route('groups.index')); ?>" class="chip-btn">
              <i data-lucide="arrow-left"></i> Back to Groups
            </a>
          <?php endif; ?>
        </div>

        <div class="groups-header-right" style="display: flex; gap: 12px;">
          <?php if($isMember): ?>
            <?php if($me->id !== $group->creator_id): ?>
              <button class="chip-btn" onclick="leaveGroup(<?php echo e($group->id); ?>)" style="color: var(--danger); border-color: #fecaca; background: #fff1f2;">
                Leave Group
              </button>
            <?php else: ?>
              <a href="<?php echo e(route('groups.edit', $group->id)); ?>" class="chip-btn" style="background: var(--hover); border-color: var(--border); text-decoration: none;">
                <i data-lucide="pencil"></i> Edit Group
              </a>
              <button type="button" class="chip-btn" onclick="deleteGroup(<?php echo e($group->id); ?>)" style="color: var(--danger); border-color: #fecaca; background: #fff1f2;">
                <i data-lucide="trash-2"></i> Delete
              </button>
            <?php endif; ?>
          <?php else: ?>
            <button class="chip-btn" onclick="joinGroup(<?php echo e($group->id); ?>)" style="background: linear-gradient(90deg, #7c3aed, #4f46e5); color: #fff; border: none; padding: 10px 20px;">
               Join Group
            </button>
          <?php endif; ?>
          
          <button class="chip-btn js-share-group" type="button" data-group-id="<?php echo e($group->id); ?>" data-preview="<?php echo e($group->name); ?>" title="Share Group">
            <i data-lucide="share-2"></i>
          </button>
        </div>
      </div>

      
      <div class="panel group-hero">
        <div class="group-hero-cover" style="background-image: url('<?php echo e($group->cover_url); ?>'); background-size: cover; background-position: center;">
          <?php if(Auth::id() === $group->creator_id): ?>
          <div class="dropdown" style="position:absolute; bottom:24px; right:24px; z-index:10;">
            <button id="coverToggleBtn" class="btn dropdown-toggle" style="background:rgba(15,23,42,0.6); backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:500; display:flex; gap:8px; align-items:center;">
              <i data-lucide="camera" style="width:16px; height:16px;"></i> Edit Cover
            </button>
            <div id="coverDropdownMenu" class="dropdown-menu" style="min-width:180px; padding:8px; border-radius:12px;">
              <label for="groupCoverInput" style="display:flex; width:100%; text-align:left; padding:10px 12px; border-radius:8px; cursor:pointer; align-items:center; gap:8px; font-size:13px; color:var(--text);" onmouseover="this.style.background='var(--hover)'" onmouseout="this.style.background='none'">
                <i data-lucide="upload" style="width:16px; height:16px;"></i> Upload New Photo
              </label>
              <button id="removeCoverBtn" onclick="deleteGroupCover(<?php echo e($group->id); ?>)" style="display: <?php echo e($group->cover_photo ? 'flex' : 'none'); ?>; width:100%; text-align:left; padding:10px 12px; border-radius:8px; background:none; border:none; color:var(--danger); align-items:center; gap:8px; font-size:13px; cursor:pointer;" onmouseover="this.style.background='var(--hover)'" onmouseout="this.style.background='none'">
                <i data-lucide="trash-2" style="width:16px; height:16px;"></i> Remove Cover
              </button>
            </div>
          </div>
          <input type="file" id="groupCoverInput" accept="image/*" style="display:none;" onchange="uploadGroupCover(this, <?php echo e($group->id); ?>)">
          <?php endif; ?>
        </div>

        <div class="group-hero-body">
          <div class="group-hero-top">
            <h1 class="group-hero-title"><?php echo e($group->name); ?></h1>
            
            <div class="group-stats">
              <div class="group-stats-item">
                <i data-lucide="users"></i> <?php echo e(number_format($group->members_count)); ?> members
              </div>
              <div class="group-stats-item group-active-stat" style="margin-left: 16px;">
                <i data-lucide="trending-up"></i> <?php echo e($group->activity_level); ?>

              </div>
            </div>
          </div>

          <p class="group-hero-desc" id="groupDesc"><?php echo e($group->description); ?></p>
          <button type="button" class="read-more-btn" id="descReadMore" style="display:none;">Read More</button>

          <div class="group-mod-section">
            <h4 style="font-size:13px; color:var(--muted); margin-bottom:10px; font-weight:700; text-transform: uppercase; letter-spacing: 0.5px;">Moderators</h4>
            <div class="group-mod-list">
              <div class="group-mod-avatars">
                <?php if($group->creator): ?>
                  <img src="<?php echo e($group->creator->avatar_url); ?>" alt="<?php echo e($group->creator->full_name); ?>" title="Creator: <?php echo e($group->creator->full_name); ?>">
                <?php endif; ?>
                <?php $__currentLoopData = $group->members->where('role', 'admin'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $adminMember): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php if(!$group->creator || $group->creator->id !== $adminMember->user_id): ?>
                    <img src="<?php echo e($adminMember->user->avatar_url); ?>" alt="<?php echo e($adminMember->user->full_name); ?>" title="Moderator: <?php echo e($adminMember->user->full_name); ?>">
                  <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      
      <div class="group-feed-container" style="margin-top: 24px;">
        <?php if($isMember): ?>
          
          <input type="hidden" id="dashGroupId" value="<?php echo e($group->id); ?>">
          
          <div class="panel composer" id="composerPanel" style="margin-bottom: 24px;">
            <div class="composer-top">
              <div class="avatar sm">
                <img src="<?php echo e($avatarUrl); ?>" alt="You" />
              </div>
              <textarea id="dashPostText" placeholder="Share your thoughts with the group..."></textarea>
            </div>
            <div id="mediaPreviewArea" class="media-preview-grid" style="display:none;"></div>
            <div class="hashtag-row" id="hashtagRow" style="display:none;">
              <i data-lucide="hash"></i>
              <input type="text" id="hashtagInput" placeholder="anxiety, hope, recovery (comma-separated)" />
            </div>

            <div class="mood-bar" id="moodBar" style="display:none;">
              <span class="mood-label">How are you feeling?</span>
              <div class="mood-options">
                <button class="mood-btn" type="button" data-mood="😊 Happy">😊 Happy</button>
                <button class="mood-btn" type="button" data-mood="😔 Sad">😔 Sad</button>
                <button class="mood-btn" type="button" data-mood="😰 Anxious">😰 Anxious</button>
                <button class="mood-btn" type="button" data-mood="😤 Stressed">😤 Stressed</button>
                <button class="mood-btn" type="button" data-mood="🥰 Grateful">🥰 Grateful</button>
                <button class="mood-btn" type="button" data-mood="😴 Tired">😴 Tired</button>
                <button class="mood-btn" type="button" data-mood="💪 Motivated">💪 Motivated</button>
                <button class="mood-btn" type="button" data-mood="😌 Calm">😌 Calm</button>
              </div>
              <div id="selectedMoodDisplay" class="selected-mood" style="display:none;"></div>
            </div>

            <div class="composer-bottom">
              <label class="chip-btn" for="mediaUpload" title="Attach photo/video" style="cursor:pointer;">
                <i data-lucide="image"></i> Photo
              </label>
              <input type="file" id="mediaUpload" accept="image/*,video/*" multiple style="display:none;" />
              <button class="chip-btn" type="button" id="moodToggleBtn" title="Add mood"><i data-lucide="smile"></i> Mood</button>
              <button class="chip-btn" type="button" id="hashtagToggleBtn" title="Add hashtags"><i data-lucide="hash"></i> Tags</button>
              <div class="link-popup-wrap" id="linkWrap">
                <button class="chip-btn" type="button" id="linkToggleBtn" title="Add link">
                  <i data-lucide="link"></i> Link
                </button>
                <div class="link-popup-card" id="linkRow" onclick="event.stopPropagation()">
                  <div class="link-popup-inputs">
                    <div class="link-popup-row">
                      <i data-lucide="type" class="link-popup-icon" style="width:16px;height:16px;"></i>
                      <input type="text" id="linkNameInput" placeholder="Text">
                    </div>
                    <div class="link-popup-row">
                      <i data-lucide="link" class="link-popup-icon" style="width:16px;height:16px;"></i>
                      <input type="url" id="linkUrlInput" placeholder="Type or paste a link" onkeydown="if(event.key==='Enter'){document.getElementById('applyLinkBtn').click();event.preventDefault();}">
                    </div>
                  </div>
                  <button type="button" class="link-popup-apply" id="applyLinkBtn">Apply</button>
                </div>
              </div>
              <div id="composerFeedback" class="composer-feedback"></div>
              <button class="share-btn" type="button" id="dashShareBtn">
                Share <i data-lucide="send"></i>
              </button>
            </div>
          </div>

          
          <div id="dashFeed">
            <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <?php echo $__env->make('profile._post', ['post' => $post, 'me' => $me, 'group' => $group], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <div id="feedEmpty" class="empty-state panel">
                <i data-lucide="file-text"></i>
                <p>No posts yet. Be the first to share something!</p>
              </div>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div class="panel empty-state">
            <i data-lucide="lock"></i>
            <p>You must join this group to view and create posts.</p>
          </div>
        <?php endif; ?>
      </div>
  </div>
</main>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
async function deleteGroup(id) {
    const result = await Swal.fire({
        title: 'Delete this group?',
        text: 'This will permanently delete the group and all its posts.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete it'
    });

    if (!result.isConfirmed) return;

    try {
        const res = await fetch(`/groups/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (data.ok && data.redirect) {
            window.location.href = data.redirect;
        } else {
            Swal.fire('Error', data.message || 'Failed to delete group.', 'error');
        }
    } catch (e3) {
        Swal.fire('Error', 'Network error. Please try again.', 'error');
    }
}

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

async function leaveGroup(id) {
    try {
        let res = await fetch(`/groups/${id}/leave`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>', 'Accept': 'application/json' }
        });
        let data = await res.json();
        if(data.ok) location.reload();
    } catch(e) {}
}

async function uploadGroupCover(input, groupId) {
    if (!input.files || !input.files[0]) return;
    
    // Instantly preview the image using a temporary object URL
    const file = input.files[0];
    const objectUrl = URL.createObjectURL(file);
    const heroCover = document.querySelector('.group-hero-cover');
    if (heroCover) {
        heroCover.style.background = 'none';
        heroCover.style.backgroundImage = `url("${objectUrl}")`;
        heroCover.style.backgroundSize = 'cover';
        heroCover.style.backgroundPosition = 'center';
    }

    // Close the dropdown immediately
    const dropdownMenu = document.getElementById('coverDropdownMenu');
    if (dropdownMenu) dropdownMenu.classList.remove('open');

    let fd = new FormData();
    fd.append('cover_photo', file);

    try {
        let res = await fetch(`/groups/${groupId}/update-cover`, {
            method: 'POST',
            body: fd,
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Accept': 'application/json'
            }
        });
        let data = await res.json();
        
        if (data.ok) {
            Swal.fire({
                title: 'Cover Updated',
                text: 'Your group cover photo has been successfully changed.',
                icon: 'success',
                confirmButtonColor: '#7c3aed'
            }).then(() => {
                const removeBtn = document.getElementById('removeCoverBtn');
                if (removeBtn) {
                    removeBtn.style.display = 'flex';
                } else {
                     const dropdownMenu = document.getElementById('coverDropdownMenu');
                     if(dropdownMenu) {
                         const btnHTML = `
                         <button id="removeCoverBtn" onclick="deleteGroupCover(${groupId})" style="width:100%; text-align:left; padding:10px 12px; border-radius:8px; background:none; border:none; color:var(--danger); display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;" onmouseover="this.style.background='var(--hover)'" onmouseout="this.style.background='none'">
                           <i data-lucide="trash-2" style="width:16px; height:16px;"></i> Remove Cover
                         </button>`;
                         dropdownMenu.insertAdjacentHTML('beforeend', btnHTML);
                         if(window.lucide) lucide.createIcons();
                     }
                }
            });
            input.value = ''; // Reset input to allow selecting the same file again
        } else {
            Swal.fire('Error', data.message || 'Failed to update cover photo.', 'error');
        }
    } catch (e) {
        console.error(e);
        Swal.fire('Error', 'An unexpected error occurred.', 'error');
    }
}

async function deleteGroupCover(groupId) {
    const result = await Swal.fire({
        title: 'Remove Cover Photo?',
        text: 'This will permanently remove the custom cover photo and revert to the default gradient.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, remove it'
    });

    if (result.isConfirmed) {
        try {
            let res = await fetch(`/groups/${groupId}/delete-cover`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'Accept': 'application/json'
                }
            });
            let data = await res.json();
            
            if (data.ok) {
                Swal.fire({
                    title: 'Removed!',
                    text: 'The cover photo has been removed.',
                    icon: 'success',
                    confirmButtonColor: '#7c3aed'
                }).then(() => {
                    const heroCover = document.querySelector('.group-hero-cover');
                    if (heroCover) {
                        heroCover.style.backgroundImage = 'none';
                        heroCover.style.background = 'linear-gradient(135deg, #1e293b 0%, #0f172a 100%)';
                        heroCover.style.backgroundSize = 'auto';
                        heroCover.style.backgroundPosition = '0% 0%';
                    }
                    const removeBtn = document.getElementById('removeCoverBtn');
                    if(removeBtn) {
                        removeBtn.style.display = 'none';
                    }
                });
            } else {
                Swal.fire('Error', data.message || 'Failed to remove cover photo.', 'error');
            }
        } catch (e) {
            console.error(e);
            Swal.fire('Error', 'An unexpected error occurred.', 'error');
        }
    }
    
    // Close the dropdown immediately
    const dropdownMenu = document.getElementById('coverDropdownMenu');
    if (dropdownMenu) dropdownMenu.classList.remove('open');
}

document.addEventListener('DOMContentLoaded', function() {
    const coverToggleBtn = document.getElementById('coverToggleBtn');
    const coverDropdownMenu = document.getElementById('coverDropdownMenu');

    if (coverToggleBtn && coverDropdownMenu) {
        coverToggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            coverDropdownMenu.classList.toggle('open');
        });

        document.addEventListener('click', function(e) {
            if (!coverToggleBtn.contains(e.target) && !coverDropdownMenu.contains(e.target)) {
                coverDropdownMenu.classList.remove('open');
            }
        });
    }

    // Read More Logic for Description
    const desc = document.getElementById('groupDesc');
    const descBtn = document.getElementById('descReadMore');
    if (desc && descBtn) {
        if (desc.scrollHeight > desc.clientHeight) {
            descBtn.style.display = 'block';
        }
        descBtn.addEventListener('click', function() {
            desc.classList.toggle('expanded');
            this.textContent = desc.classList.contains('expanded') ? 'Read Less' : 'Read More';
        });
    }

    // Read More Logic for Guidelines
    document.querySelectorAll('.group-guidelines-list li').forEach(li => {
        const text = li.querySelector('.guideline-text');
        const btn = li.querySelector('.guideline-toggle');
        if (text && btn) {
            // Add initial clamping class to the div if not already on li
            text.style.display = '-webkit-box';
            text.style.webkitLineClamp = '3';
            text.style.webkitBoxOrient = 'vertical';
            text.style.overflow = 'hidden';

            if (text.scrollHeight > text.clientHeight) {
                btn.style.display = 'block';
            }

            btn.addEventListener('click', function() {
                if (text.style.webkitLineClamp === '3') {
                    text.style.webkitLineClamp = 'unset';
                    this.textContent = 'Read Less';
                } else {
                    text.style.webkitLineClamp = '3';
                    this.textContent = 'Read More';
                }
            });
        }
    });
});
</script>

<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.dashboard', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\websystem\resources\views/groups/show.blade.php ENDPATH**/ ?>