

<?php $__env->startSection('title', $profileUser->full_name . ' – AskDocPH'); ?>

<?php $__env->startPush('styles'); ?>
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/profile.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
  $me        = Auth::user();
  $isOwn     = $me && $me->id === $profileUser->id;
  $avatarUrl = $profileUser->avatar_url;
  $fullName  = $profileUser->short_name ?: $profileUser->full_name;
  $shortName = $me ? ($me->short_name ?: $me->full_name) : '';
  $username  = $profileUser->username ?? 'username';
  $groupsJoinedCount = isset($joinedGroups) ? $joinedGroups->count() : 0;
  $resourcesJoinedCount = isset($joinedResources) ? $joinedResources->count() : 0;
  $hasCreatedGroups = isset($createdGroups) && $createdGroups->isNotEmpty();
  $hasCreatedResources = isset($createdResources) && $createdResources->isNotEmpty();
  $isVerifiedDoctor = $profileUser->role === 'doctor' && $profileUser->doctor_status === 'approved';
?>


<main class="dash">
  <div class="dash-body">
    
    <?php echo $__env->make('partials.sidebar', ['active' => $isOwn ? 'profile' : ''], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <main class="prof-main">

      
      <div class="panel prof-card">
        <div class="prof-cover" id="coverDisplay" data-view-image data-fullsrc="<?php echo e($profileUser->cover_url); ?>" style="background-image: url('<?php echo e($profileUser->cover_url); ?>'); background-size: cover; background-position: center;">
          <?php if(!$profileUser->cover_photo): ?>
          <div class="prof-cover-grad" id="coverGradientOverlay"></div>
          <?php endif; ?>
          
          <?php if($isOwn): ?>
          <div class="prof-cover-actions">
            <label for="coverUpload" class="cover-action-btn" title="Update cover photo">
              <i data-lucide="camera"></i> Edit Cover
            </label>
            <button type="button" class="cover-action-btn danger <?php echo e($profileUser->cover_photo ? '' : 'hidden'); ?>" id="deleteCoverBtn" title="Remove cover photo">
              <i data-lucide="trash-2"></i>
            </button>
            <input type="file" id="coverUpload" accept="image/*" class="hidden-input">
          </div>
          <?php endif; ?>
        </div>

        <div class="prof-card-body">
          
          <div class="prof-avatar-wrap">
            <img src="<?php echo e($avatarUrl); ?>" alt="<?php echo e($fullName); ?>" class="prof-avatar" id="previewAvatar" data-view-image data-fullsrc="<?php echo e($avatarUrl); ?>">
            <?php if($isOwn): ?>
            <div class="prof-avatar-actions">
              <label for="photoUpload" class="avatar-action-btn" title="Change photo">
                <i data-lucide="camera"></i>
              </label>
              <button type="button" class="avatar-action-btn danger" id="deletePhotoBtn" title="Remove photo">
                <i data-lucide="trash-2"></i>
              </button>
            </div>
            <input type="file" id="photoUpload" accept="image/*" class="hidden-input">
            <?php endif; ?>
          </div>

          <div class="prof-card-info">
            <div class="prof-card-names">
              <div class="prof-name-row">
                <h1 class="prof-fullname"><?php echo e($fullName); ?></h1>
                <span class="prof-handle" id="profHandle"><?php echo e('@' . $username); ?></span>
                <?php if($isVerifiedDoctor): ?>
                  <i data-lucide="badge-check" class="doctor-badge" title="Verified Doctor"></i>
                <?php endif; ?>
              </div>
              <?php if($profileUser->professional_title): ?>
                <div class="prof-title"><?php echo e($profileUser->professional_title); ?></div>
              <?php endif; ?>
            </div>

            <?php if($profileUser->bio): ?>
            <p class="prof-bio" id="bioDisplay"><?php echo e($profileUser->bio); ?></p>
            <?php else: ?>
            <p class="prof-bio muted" id="bioDisplay"><?php echo e($isOwn ? 'Add a short bio…' : 'No bio yet.'); ?></p>
            <?php endif; ?>

            <div class="prof-stats">
              <div class="stat-item">
                <span class="stat-num" id="postCountBadge"><?php echo e($posts->count()); ?></span>
                <span class="stat-lbl">Posts</span>
              </div>
              <div class="stat-item">
                <span class="stat-num"><?php echo e($groupsJoinedCount); ?></span>
                <span class="stat-lbl">Groups</span>
              </div>
              <div class="stat-item">
                <span class="stat-num"><?php echo e($resourcesJoinedCount); ?></span>
                <span class="stat-lbl">Resources</span>
              </div>
            </div>
          </div>

          <?php if($isOwn): ?>
          <button class="edit-profile-btn" id="editProfileBtn" type="button">
            <i data-lucide="pencil"></i> Edit Profile
          </button>
          <?php elseif($me): ?>
          <button class="follow-btn <?php echo e($isFollowing ? 'following' : ''); ?>" id="followBtn" type="button" data-user-id="<?php echo e($profileUser->id); ?>" data-following="<?php echo e($isFollowing ? '1' : '0'); ?>">
            <i data-lucide="<?php echo e($isFollowing ? 'user-check' : 'user-plus'); ?>"></i>
            <?php echo e($isFollowing ? 'Following' : 'Follow'); ?>

          </button>
          <?php endif; ?>
        </div>
      </div>

      
      <?php if($isOwn): ?>
      <div class="modal-backdrop" id="editModal">
        <div class="modal-box">
          <div class="modal-header">
            <h2>Edit Profile</h2>
            <button class="modal-close" id="closeEditModal" type="button"><i data-lucide="x"></i></button>
          </div>
          <form id="editProfileForm" novalidate>
            <?php echo csrf_field(); ?>
            <div class="form-row">
              <div class="form-group">
                <label>First Name</label>
                <input type="text" name="fname" id="inp_fname" value="<?php echo e($profileUser->fname); ?>" required>
              </div>
              <div class="form-group">
                <label>Middle Name</label>
                <input type="text" name="mname" id="inp_mname" value="<?php echo e($profileUser->mname); ?>">
              </div>
              <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="lname" id="inp_lname" value="<?php echo e($profileUser->lname); ?>" required>
              </div>
            </div>
            <div class="form-group">
              <label>Username</label>
              <div class="input-prefix-wrap">
                <span class="input-prefix">@</span>
                <input type="text" name="username" id="inp_username" value="<?php echo e($profileUser->username); ?>" required>
              </div>
            </div>
            <div class="form-group">
              <label>Bio <span class="muted">(max 300 chars)</span></label>
              <textarea name="bio" id="inp_bio" maxlength="300" rows="3"><?php echo e($profileUser->bio); ?></textarea>
              <div class="char-count"><span id="bioCharCount"><?php echo e(strlen($profileUser->bio ?? '')); ?></span>/300</div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn-cancel" id="cancelEditBtn">Cancel</button>
              <button type="submit" class="btn-save" id="saveProfileBtn">
                <i data-lucide="save"></i> Save Changes
              </button>
            </div>
            <div class="form-feedback" id="editFeedback"></div>
          </form>
        </div>
      </div>
      <?php endif; ?>

      
      <div class="panel prof-tabs-wrap">
        <nav class="prof-tabs" id="profTabs">
          <button class="tab-btn active" data-tab="posts">
            <i data-lucide="file-text"></i> Posts
          </button>
          <button class="tab-btn" data-tab="groups">
            <i data-lucide="users"></i> Groups
          </button>
          <button class="tab-btn" data-tab="resources">
            <i data-lucide="book-open"></i> Resources
          </button>
          <button class="tab-btn" data-tab="network">
            <i data-lucide="users"></i> Network
          </button>
          <?php if($isOwn): ?>
          <button class="tab-btn" data-tab="saved">
            <i data-lucide="bookmark"></i> Saved
          </button>
          <?php endif; ?>
          <?php if($isOwn && $profileUser->doctor_status !== 'none' && $profileUser->doctor_status !== null): ?>
          <button class="tab-btn" data-tab="application">
            <i data-lucide="stethoscope"></i> Application
          </button>
          <?php endif; ?>
        </nav>
      </div>

      
      <div class="tab-content" id="tab-posts">

        
        <?php if($isOwn): ?>
        <div class="panel composer">
          <div class="composer-top">
            <div class="avatar sm"><img src="<?php echo e($avatarUrl); ?>" alt="User"></div>
            <textarea id="postText" placeholder="Share your thoughts, feelings, or progress…" rows="3"></textarea>
          </div>

          
          <div class="composer-preview" id="mediaPreviewArea" style="display:none;"></div>

          
          <div class="hashtag-row" id="hashtagRow" style="display:none;">
            <i data-lucide="hash"></i>
            <input type="text" id="hashtagInput" placeholder="anxiety, hope, recovery  (comma-separated)" />
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
            <label for="postMedia" class="chip-btn" style="cursor:pointer;">
              <i data-lucide="image"></i> Photo
            </label>
            <input type="file" id="postMedia" accept="image/*,video/*" multiple class="hidden-input">
            
            <button class="chip-btn" type="button" id="moodToggleBtn" title="Add mood">
              <i data-lucide="smile"></i> Mood
            </button>

            <button class="chip-btn" type="button" id="hashtagToggleBtn" title="Add hashtags">
              <i data-lucide="hash"></i> Tags
            </button>

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

            <button class="share-btn" type="button" id="submitPostBtn">
              Share <i data-lucide="send"></i>
            </button>
          </div>
          <div class="composer-feedback" id="postFeedback"></div>
        </div>
        <?php endif; ?>

        
        <div id="postsFeed">
          <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <?php echo $__env->make('profile._post', ['post' => $post, 'isOwn' => $isOwn, 'me' => $me], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <div class="empty-state panel">
            <i data-lucide="file-text"></i>
            <p>No posts yet.</p>
          </div>
          <?php endif; ?>
        </div>
      </div>

      
      <div class="tab-content hidden" id="tab-groups">
        <div class="panel prof-section" data-section="groups" data-current="joined">
          <div class="prof-section-header">
            <div class="prof-section-title">
              <i data-lucide="users"></i>
              <span>My Groups</span>
            </div>
            <div class="prof-section-tools">
              <div class="prof-section-search">
                <i data-lucide="search"></i>
                <input type="text" class="prof-section-search-input" data-prof-search="groups" placeholder="Search groups...">
              </div>
            <?php if($isVerifiedDoctor): ?>
              <div class="prof-filter-dropdown" data-target="groups">
                <button type="button" class="prof-filter-toggle" data-current="joined">
                  <span>Joined</span>
                  <i data-lucide="chevron-down"></i>
                </button>
                <div class="prof-filter-menu">
                  <button type="button" data-value="joined">Joined</button>
                  <button type="button" data-value="created">Created</button>
                </div>
              </div>
            <?php endif; ?>
            </div>
          </div>

          <div class="prof-section-body">
            
            <div class="prof-grid prof-grid-groups prof-groups-joined">
              <?php $__empty_1 = true; $__currentLoopData = $joinedGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e(route('groups.show', $group->id)); ?>?from=profile&profile_id=<?php echo e($profileUser->id); ?>&tab=groups" class="prof-card prof-group-card">
                  <div class="prof-group-thumb" style="background-image:url('<?php echo e($group->cover_url); ?>');"></div>
                  <div class="prof-card-main">
                    <div class="prof-card-title-row">
                      <span class="prof-card-title"><?php echo e($group->name); ?></span>
                      <?php if($group->visibility === 'private'): ?>
                        <span class="prof-badge muted">Private</span>
                      <?php else: ?>
                        <span class="prof-badge">Public</span>
                      <?php endif; ?>
                    </div>
                    <?php if($group->description): ?>
                      <p class="prof-card-desc"><?php echo e(\Illuminate\Support\Str::limit($group->description, 120)); ?></p>
                    <?php endif; ?>
                  </div>
                  <div class="prof-card-meta">
                    <span class="prof-meta-item">
                      <i data-lucide="users"></i>
                      <span><?php echo e($group->members_count ?? $group->members()->count()); ?> members</span>
                    </span>
                  </div>
                </a>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="empty-state soft">
                  <i data-lucide="users"></i>
                  <p>No joined groups yet.</p>
                </div>
              <?php endif; ?>
            </div>

            
            <div class="prof-grid prof-grid-groups prof-groups-created">
              <?php $__empty_1 = true; $__currentLoopData = $createdGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e(route('groups.show', $group->id)); ?>?from=profile&profile_id=<?php echo e($profileUser->id); ?>&tab=groups" class="prof-card prof-group-card">
                  <div class="prof-group-thumb" style="background-image:url('<?php echo e($group->cover_url); ?>');"></div>
                  <div class="prof-card-main">
                    <div class="prof-card-title-row">
                      <span class="prof-card-title"><?php echo e($group->name); ?></span>
                      <?php if($group->visibility === 'private'): ?>
                        <span class="prof-badge muted">Private</span>
                      <?php else: ?>
                        <span class="prof-badge">Public</span>
                      <?php endif; ?>
                    </div>
                    <?php if($group->description): ?>
                      <p class="prof-card-desc"><?php echo e(\Illuminate\Support\Str::limit($group->description, 120)); ?></p>
                    <?php endif; ?>
                  </div>
                  <div class="prof-card-meta">
                    <span class="prof-meta-item">
                      <i data-lucide="users"></i>
                      <span><?php echo e($group->members_count ?? $group->members()->count()); ?> members</span>
                    </span>
                  </div>
                </a>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="empty-state soft">
                  <i data-lucide="users"></i>
                  <p>No created groups yet.</p>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      
      <div class="tab-content hidden" id="tab-resources">
        <div class="panel prof-section" data-section="resources" data-current="joined">
          <div class="prof-section-header">
            <div class="prof-section-title">
              <i data-lucide="book-open"></i>
              <span>My Resources</span>
            </div>
            <div class="prof-section-tools">
              <div class="prof-section-search">
                <i data-lucide="search"></i>
                <input type="text" class="prof-section-search-input" data-prof-search="resources" placeholder="Search resources...">
              </div>
            <?php if($isVerifiedDoctor): ?>
              <div class="prof-filter-dropdown" data-target="resources">
                <button type="button" class="prof-filter-toggle" data-current="joined">
                  <span>Joined</span>
                  <i data-lucide="chevron-down"></i>
                </button>
                <div class="prof-filter-menu">
                  <button type="button" data-value="joined">Joined</button>
                  <button type="button" data-value="created">Created</button>
                </div>
              </div>
            <?php endif; ?>
            </div>
          </div>

          <div class="prof-section-body">
            
            <div class="prof-grid prof-grid-resources prof-resources-joined">
              <?php $__empty_1 = true; $__currentLoopData = $joinedResources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $res): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e(route('resources.show', $res->id)); ?>?from=profile&profile_id=<?php echo e($profileUser->id); ?>&tab=resources" class="prof-card prof-resource-card">
                  <div class="prof-res-thumb" style="background-image:url('<?php echo e($res->thumbnail_url); ?>');"></div>
                  <div class="prof-card-main">
                    <div class="prof-card-title-row">
                      <span class="prof-card-title"><?php echo e($res->title); ?></span>
                      <?php if($res->type): ?>
                        <span class="prof-badge"><?php echo e(ucfirst($res->type)); ?></span>
                      <?php endif; ?>
                    </div>
                    <?php if($res->description): ?>
                      <p class="prof-card-desc"><?php echo e(\Illuminate\Support\Str::limit($res->description, 140)); ?></p>
                    <?php endif; ?>
                  </div>
                  <div class="prof-card-meta">
                    <span class="prof-meta-item">
                      <i data-lucide="user"></i>
                      <span><?php echo e($res->user->short_name ?: $res->user->full_name); ?></span>
                    </span>
                    <?php if($res->duration_meta): ?>
                      <span class="prof-meta-item">
                        <i data-lucide="clock"></i>
                        <span><?php echo e($res->duration_meta); ?></span>
                      </span>
                    <?php endif; ?>
                  </div>
                </a>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="empty-state soft">
                  <i data-lucide="book-open"></i>
                  <p>No joined resources yet.</p>
                </div>
              <?php endif; ?>
            </div>

            
            <div class="prof-grid prof-grid-resources prof-resources-created">
              <?php $__empty_1 = true; $__currentLoopData = $createdResources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $res): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e(route('resources.show', $res->id)); ?>?from=profile&profile_id=<?php echo e($profileUser->id); ?>&tab=resources" class="prof-card prof-resource-card">
                  <div class="prof-res-thumb" style="background-image:url('<?php echo e($res->thumbnail_url); ?>');"></div>
                  <div class="prof-card-main">
                    <div class="prof-card-title-row">
                      <span class="prof-card-title"><?php echo e($res->title); ?></span>
                      <?php if($res->type): ?>
                        <span class="prof-badge"><?php echo e(ucfirst($res->type)); ?></span>
                      <?php endif; ?>
                    </div>
                    <?php if($res->description): ?>
                      <p class="prof-card-desc"><?php echo e(\Illuminate\Support\Str::limit($res->description, 140)); ?></p>
                    <?php endif; ?>
                  </div>
                  <div class="prof-card-meta">
                    <span class="prof-meta-item">
                      <i data-lucide="user"></i>
                      <span><?php echo e($res->user->short_name ?: $res->user->full_name); ?></span>
                    </span>
                    <?php if($res->duration_meta): ?>
                      <span class="prof-meta-item">
                        <i data-lucide="clock"></i>
                        <span><?php echo e($res->duration_meta); ?></span>
                      </span>
                    <?php endif; ?>
                  </div>
                </a>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="empty-state soft">
                  <i data-lucide="book-open"></i>
                  <p>No created resources yet.</p>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      
      <div class="tab-content hidden" id="tab-network">
        <div class="panel">
          <div class="prof-section-header" style="padding: 10px 10px 0;">
            <div class="prof-section-title">
              <i data-lucide="users"></i>
              <span>Network</span>
            </div>
          </div>
          <div class="prof-section-body" style="padding: 8px 10px 0;">
            <div class="network-controls">
              <div class="network-tabs" id="networkTabs">
                <button class="network-tab active" data-filter="following">Following</button>
                <button class="network-tab" data-filter="followers">Followers</button>
              </div>
              <div class="network-search">
                <i data-lucide="search"></i>
                <input type="text" id="networkSearch" placeholder="Search name or @username">
              </div>
            </div>

            <div class="prof-user-list" id="networkList">
              <?php $__empty_1 = true; $__currentLoopData = $following; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e(route('profile.show', $u->id)); ?>" class="prof-user-row" data-type="following" data-name="<?php echo e(strtolower($u->full_name)); ?>" data-username="<?php echo e(strtolower($u->username)); ?>">
                  <div class="avatar sm"><img src="<?php echo e($u->avatar_url); ?>" alt="<?php echo e($u->full_name); ?>"></div>
                  <div class="prof-user-meta">
                    <div class="prof-user-name"><?php echo e($u->short_name ?: $u->full_name); ?></div>
                    <div class="prof-user-handle"><?php echo e('@' . $u->username); ?></div>
                  </div>
                </a>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="empty-state soft" data-type="following">
                  <i data-lucide="user-plus"></i>
                  <p>No following yet.</p>
                </div>
              <?php endif; ?>

              <?php $__empty_1 = true; $__currentLoopData = $followers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e(route('profile.show', $u->id)); ?>" class="prof-user-row" data-type="followers" data-name="<?php echo e(strtolower($u->full_name)); ?>" data-username="<?php echo e(strtolower($u->username)); ?>" style="display:none;">
                  <div class="avatar sm"><img src="<?php echo e($u->avatar_url); ?>" alt="<?php echo e($u->full_name); ?>"></div>
                  <div class="prof-user-meta">
                    <div class="prof-user-name"><?php echo e($u->short_name ?: $u->full_name); ?></div>
                    <div class="prof-user-handle"><?php echo e('@' . $u->username); ?></div>
                  </div>
                </a>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="empty-state soft" data-type="followers" style="display:none;">
                  <i data-lucide="users"></i>
                  <p>No followers yet.</p>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      
      <?php if($isOwn): ?>
      <div class="tab-content hidden" id="tab-saved">
        <div class="panel">
          <div class="prof-section-header" style="padding: 10px 10px 0;">
            <div class="prof-section-title">
              <i data-lucide="bookmark"></i>
              <span>Saved Posts</span>
            </div>
          </div>
          <div class="prof-section-body" style="padding: 8px 10px 0;">
            <?php if(isset($savedPosts) && $savedPosts->count()): ?>
              <div id="savedPostsFeed">
                <?php $__currentLoopData = $savedPosts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php echo $__env->make('profile._post', ['post' => $post, 'isOwn' => $isOwn, 'me' => $me], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </div>
            <?php else: ?>
              <div class="empty-state">
                <i data-lucide="bookmark"></i>
                <p>No saved posts yet. Tap the bookmark on any post to save it here.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      
      <?php if($isOwn && $profileUser->doctor_status !== 'none' && $profileUser->doctor_status !== null): ?>
      <div class="tab-content hidden" id="tab-application">
        <?php echo $__env->make('profile._application', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      </div>
      <?php endif; ?>

    </main>
  </div>
</main>


<div class="toast" id="toast"></div>


<script>
  window.PROFILE_USER_ID = <?php echo e($profileUser->id); ?>;
  window.IS_OWN_PROFILE  = <?php echo e($isOwn ? 'true' : 'false'); ?>;
  window.AUTH_USER_ID    = <?php echo e($me ? $me->id : 'null'); ?>;
  window.ROUTES = {
    updateInfo:    '<?php echo e(route('profile.update.info')); ?>',
    updatePhoto:   '<?php echo e(route('profile.update.photo')); ?>',
    deletePhoto:   '<?php echo e(route('profile.delete.photo')); ?>',
    updateCover:   '<?php echo e(route('profile.update.cover')); ?>',
    deleteCover:   '<?php echo e(route('profile.delete.cover')); ?>',
    storePost:     '<?php echo e(route('profile.posts.store')); ?>',
    updatePost:    function(id){ return '/profile/posts/' + id; },
    destroyPost:   function(id){ return '/profile/posts/' + id; },
    toggleLike:    function(id){ return '/profile/posts/' + id + '/like'; },
    toggleSave:    function(id){ return '/profile/posts/' + id + '/save'; },
    toggleFollow:  function(id){ return '/profile/' + id + '/follow'; },
    storeComment:  function(id){ return '/profile/posts/' + id + '/comments'; },
    destroyComment:function(id){ return '/profile/comments/' + id; },
    profileNetwork:function(id){ return '/api/profile/' + id + '/network'; },
  };
</script>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
  <script src="<?php echo e(asset('assets/js/profile.js?v=' . time())); ?>" defer></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.dashboard', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\websystem\resources\views/profile/show.blade.php ENDPATH**/ ?>