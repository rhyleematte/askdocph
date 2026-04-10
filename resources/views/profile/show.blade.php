@extends('layouts.dashboard')

@section('title', $profileUser->full_name . ' – AskDocPH')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/profile.css') }}">
@endpush

@section('content')
@php
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
@endphp

{{-- ════════════════════════════════════════════════════════════════
     PROFILE WRAPPER
═══════════════════════════════════════════════════════════════════ --}}
<main class="dash">
  <div class="dash-body">
    {{-- ══ LEFT – shared sidebar ══ --}}
    @include('partials.sidebar', ['active' => $isOwn ? 'profile' : ''])

    {{-- ══ CENTER – profile content ══ --}}
    <main class="prof-main">

      {{-- ─ Cover + Avatar card ─ --}}
      <div class="panel prof-card">
        <div class="prof-cover" id="coverDisplay" data-view-image data-fullsrc="{{ $profileUser->cover_url }}" style="background-image: url('{{ $profileUser->cover_url }}'); background-size: cover; background-position: center;">
          @if(!$profileUser->cover_photo)
          <div class="prof-cover-grad" id="coverGradientOverlay"></div>
          @endif
          
          @if($isOwn)
          <div class="prof-cover-actions">
            <label for="coverUpload" class="cover-action-btn" title="Update cover photo">
              <i data-lucide="camera"></i> Edit Cover
            </label>
            <button type="button" class="cover-action-btn danger {{ $profileUser->cover_photo ? '' : 'hidden' }}" id="deleteCoverBtn" title="Remove cover photo">
              <i data-lucide="trash-2"></i>
            </button>
            <input type="file" id="coverUpload" accept="image/*" class="hidden-input">
          </div>
          @endif
        </div>

        <div class="prof-card-body">
          {{-- Avatar with upload overlay --}}
          <div class="prof-avatar-wrap">
            <img src="{{ $avatarUrl }}" alt="{{ $fullName }}" class="prof-avatar" id="previewAvatar" data-view-image data-fullsrc="{{ $avatarUrl }}">
            @if($isOwn)
            <div class="prof-avatar-actions">
              <label for="photoUpload" class="avatar-action-btn" title="Change photo">
                <i data-lucide="camera"></i>
              </label>
              <button type="button" class="avatar-action-btn danger" id="deletePhotoBtn" title="Remove photo">
                <i data-lucide="trash-2"></i>
              </button>
            </div>
            <input type="file" id="photoUpload" accept="image/*" class="hidden-input">
            @endif
          </div>

          <div class="prof-card-info">
            <div class="prof-card-names">
              <div class="prof-name-row">
                <h1 class="prof-fullname">{{ $fullName }}</h1>
                <span class="prof-handle" id="profHandle">{{ '@' . $username }}</span>
                @if($isVerifiedDoctor)
                  <i data-lucide="badge-check" class="doctor-badge" title="Verified Doctor"></i>
                @endif
              </div>
              @if($profileUser->professional_title)
                <div class="prof-title">{{ $profileUser->professional_title }}</div>
              @endif
            </div>

            @if($profileUser->bio)
            <p class="prof-bio" id="bioDisplay">{{ $profileUser->bio }}</p>
            @else
            <p class="prof-bio muted" id="bioDisplay">{{ $isOwn ? 'Add a short bio…' : 'No bio yet.' }}</p>
            @endif

            <div class="prof-stats">
              <div class="stat-item">
                <span class="stat-num" id="postCountBadge">{{ $posts->count() }}</span>
                <span class="stat-lbl">Posts</span>
              </div>
              <div class="stat-item">
                <span class="stat-num">{{ $groupsJoinedCount }}</span>
                <span class="stat-lbl">Groups</span>
              </div>
              <div class="stat-item">
                <span class="stat-num">{{ $resourcesJoinedCount }}</span>
                <span class="stat-lbl">Resources</span>
              </div>
            </div>
          </div>

          @if($isOwn)
          <button class="edit-profile-btn" id="editProfileBtn" type="button">
            <i data-lucide="pencil"></i> Edit Profile
          </button>
          @elseif($me)
          <button class="follow-btn {{ $isFollowing ? 'following' : '' }}" id="followBtn" type="button" data-user-id="{{ $profileUser->id }}" data-following="{{ $isFollowing ? '1' : '0' }}">
            <i data-lucide="{{ $isFollowing ? 'user-check' : 'user-plus' }}"></i>
            {{ $isFollowing ? 'Following' : 'Follow' }}
          </button>
          @endif
        </div>
      </div>

      {{-- ─ Edit Profile Modal ─ --}}
      @if($isOwn)
      <div class="modal-backdrop" id="editModal">
        <div class="modal-box">
          <div class="modal-header">
            <h2>Edit Profile</h2>
            <button class="modal-close" id="closeEditModal" type="button"><i data-lucide="x"></i></button>
          </div>
          <form id="editProfileForm" novalidate>
            @csrf
            <div class="form-row">
              <div class="form-group">
                <label>First Name</label>
                <input type="text" name="fname" id="inp_fname" value="{{ $profileUser->fname }}" required>
              </div>
              <div class="form-group">
                <label>Middle Name</label>
                <input type="text" name="mname" id="inp_mname" value="{{ $profileUser->mname }}">
              </div>
              <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="lname" id="inp_lname" value="{{ $profileUser->lname }}" required>
              </div>
            </div>
            <div class="form-group">
              <label>Username</label>
              <div class="input-prefix-wrap">
                <span class="input-prefix">@</span>
                <input type="text" name="username" id="inp_username" value="{{ $profileUser->username }}" required>
              </div>
            </div>
            <div class="form-group">
              <label>Bio <span class="muted">(max 300 chars)</span></label>
              <textarea name="bio" id="inp_bio" maxlength="300" rows="3">{{ $profileUser->bio }}</textarea>
              <div class="char-count"><span id="bioCharCount">{{ strlen($profileUser->bio ?? '') }}</span>/300</div>
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
      @endif

      {{-- ─ Tabs ─ --}}
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
          @if($isOwn)
          <button class="tab-btn" data-tab="saved">
            <i data-lucide="bookmark"></i> Saved
          </button>
          @endif
          @if($isOwn && $profileUser->doctor_status !== 'none' && $profileUser->doctor_status !== null)
          <button class="tab-btn" data-tab="application">
            <i data-lucide="stethoscope"></i> Application
          </button>
          @endif
        </nav>
      </div>

      {{-- ─ Posts Tab ─ --}}
      <div class="tab-content" id="tab-posts">

        {{-- Composer (own profile only) --}}
        @if($isOwn)
        <div class="panel composer">
          <div class="composer-top">
            <div class="avatar sm"><img src="{{ $avatarUrl }}" alt="User"></div>
            <textarea id="postText" placeholder="Share your thoughts, feelings, or progress…" rows="3"></textarea>
          </div>

          {{-- Media preview --}}
          <div class="composer-preview" id="mediaPreviewArea" style="display:none;"></div>

          {{-- Hashtag input --}}
          <div class="hashtag-row" id="hashtagRow" style="display:none;">
            <i data-lucide="hash"></i>
            <input type="text" id="hashtagInput" placeholder="anxiety, hope, recovery  (comma-separated)" />
          </div>

          {{-- Mood bar --}}
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
        @endif

        {{-- Posts List --}}
        <div id="postsFeed">
          @forelse($posts as $post)
          @include('profile._post', ['post' => $post, 'isOwn' => $isOwn, 'me' => $me])
          @empty
          <div class="empty-state panel">
            <i data-lucide="file-text"></i>
            <p>No posts yet.</p>
          </div>
          @endforelse
        </div>
      </div>

      {{-- ─ Groups Tab ─ --}}
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
            @if($isVerifiedDoctor)
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
            @endif
            </div>
          </div>

          <div class="prof-section-body">
            {{-- Joined Groups --}}
            <div class="prof-grid prof-grid-groups prof-groups-joined">
              @forelse($joinedGroups as $group)
                <a href="{{ route('groups.show', $group->id) }}?from=profile&profile_id={{ $profileUser->id }}&tab=groups" class="prof-card prof-group-card">
                  <div class="prof-group-thumb" style="background-image:url('{{ $group->cover_url }}');"></div>
                  <div class="prof-card-main">
                    <div class="prof-card-title-row">
                      <span class="prof-card-title">{{ $group->name }}</span>
                      @if($group->visibility === 'private')
                        <span class="prof-badge muted">Private</span>
                      @else
                        <span class="prof-badge">Public</span>
                      @endif
                    </div>
                    @if($group->description)
                      <p class="prof-card-desc">{{ \Illuminate\Support\Str::limit($group->description, 120) }}</p>
                    @endif
                  </div>
                  <div class="prof-card-meta">
                    <span class="prof-meta-item">
                      <i data-lucide="users"></i>
                      <span>{{ $group->members_count ?? $group->members()->count() }} members</span>
                    </span>
                  </div>
                </a>
              @empty
                <div class="empty-state soft">
                  <i data-lucide="users"></i>
                  <p>No joined groups yet.</p>
                </div>
              @endforelse
            </div>

            {{-- Created Groups --}}
            <div class="prof-grid prof-grid-groups prof-groups-created">
              @forelse($createdGroups as $group)
                <a href="{{ route('groups.show', $group->id) }}?from=profile&profile_id={{ $profileUser->id }}&tab=groups" class="prof-card prof-group-card">
                  <div class="prof-group-thumb" style="background-image:url('{{ $group->cover_url }}');"></div>
                  <div class="prof-card-main">
                    <div class="prof-card-title-row">
                      <span class="prof-card-title">{{ $group->name }}</span>
                      @if($group->visibility === 'private')
                        <span class="prof-badge muted">Private</span>
                      @else
                        <span class="prof-badge">Public</span>
                      @endif
                    </div>
                    @if($group->description)
                      <p class="prof-card-desc">{{ \Illuminate\Support\Str::limit($group->description, 120) }}</p>
                    @endif
                  </div>
                  <div class="prof-card-meta">
                    <span class="prof-meta-item">
                      <i data-lucide="users"></i>
                      <span>{{ $group->members_count ?? $group->members()->count() }} members</span>
                    </span>
                  </div>
                </a>
              @empty
                <div class="empty-state soft">
                  <i data-lucide="users"></i>
                  <p>No created groups yet.</p>
                </div>
              @endforelse
            </div>
          </div>
        </div>
      </div>

      {{-- ─ Resources Tab ─ --}}
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
            @if($isVerifiedDoctor)
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
            @endif
            </div>
          </div>

          <div class="prof-section-body">
            {{-- Joined Resources --}}
            <div class="prof-grid prof-grid-resources prof-resources-joined">
              @forelse($joinedResources as $res)
                <a href="{{ route('resources.show', $res->id) }}?from=profile&profile_id={{ $profileUser->id }}&tab=resources" class="prof-card prof-resource-card">
                  <div class="prof-res-thumb" style="background-image:url('{{ $res->thumbnail_url }}');"></div>
                  <div class="prof-card-main">
                    <div class="prof-card-title-row">
                      <span class="prof-card-title">{{ $res->title }}</span>
                      @if($res->type)
                        <span class="prof-badge">{{ ucfirst($res->type) }}</span>
                      @endif
                    </div>
                    @if($res->description)
                      <p class="prof-card-desc">{{ \Illuminate\Support\Str::limit($res->description, 140) }}</p>
                    @endif
                  </div>
                  <div class="prof-card-meta">
                    <span class="prof-meta-item">
                      <i data-lucide="user"></i>
                      <span>{{ $res->user->short_name ?: $res->user->full_name }}</span>
                    </span>
                    @if($res->duration_meta)
                      <span class="prof-meta-item">
                        <i data-lucide="clock"></i>
                        <span>{{ $res->duration_meta }}</span>
                      </span>
                    @endif
                  </div>
                </a>
              @empty
                <div class="empty-state soft">
                  <i data-lucide="book-open"></i>
                  <p>No joined resources yet.</p>
                </div>
              @endforelse
            </div>

            {{-- Created Resources --}}
            <div class="prof-grid prof-grid-resources prof-resources-created">
              @forelse($createdResources as $res)
                <a href="{{ route('resources.show', $res->id) }}?from=profile&profile_id={{ $profileUser->id }}&tab=resources" class="prof-card prof-resource-card">
                  <div class="prof-res-thumb" style="background-image:url('{{ $res->thumbnail_url }}');"></div>
                  <div class="prof-card-main">
                    <div class="prof-card-title-row">
                      <span class="prof-card-title">{{ $res->title }}</span>
                      @if($res->type)
                        <span class="prof-badge">{{ ucfirst($res->type) }}</span>
                      @endif
                    </div>
                    @if($res->description)
                      <p class="prof-card-desc">{{ \Illuminate\Support\Str::limit($res->description, 140) }}</p>
                    @endif
                  </div>
                  <div class="prof-card-meta">
                    <span class="prof-meta-item">
                      <i data-lucide="user"></i>
                      <span>{{ $res->user->short_name ?: $res->user->full_name }}</span>
                    </span>
                    @if($res->duration_meta)
                      <span class="prof-meta-item">
                        <i data-lucide="clock"></i>
                        <span>{{ $res->duration_meta }}</span>
                      </span>
                    @endif
                  </div>
                </a>
              @empty
                <div class="empty-state soft">
                  <i data-lucide="book-open"></i>
                  <p>No created resources yet.</p>
                </div>
              @endforelse
            </div>
          </div>
        </div>
      </div>

      {{-- ─ Network Tab ─ --}}
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
              @forelse($following as $u)
                <a href="{{ route('profile.show', $u->id) }}" class="prof-user-row" data-type="following" data-name="{{ strtolower($u->full_name) }}" data-username="{{ strtolower($u->username) }}">
                  <div class="avatar sm"><img src="{{ $u->avatar_url }}" alt="{{ $u->full_name }}"></div>
                  <div class="prof-user-meta">
                    <div class="prof-user-name">{{ $u->short_name ?: $u->full_name }}</div>
                    <div class="prof-user-handle">{{ '@' . $u->username }}</div>
                  </div>
                </a>
              @empty
                <div class="empty-state soft" data-type="following">
                  <i data-lucide="user-plus"></i>
                  <p>No following yet.</p>
                </div>
              @endforelse

              @forelse($followers as $u)
                <a href="{{ route('profile.show', $u->id) }}" class="prof-user-row" data-type="followers" data-name="{{ strtolower($u->full_name) }}" data-username="{{ strtolower($u->username) }}" style="display:none;">
                  <div class="avatar sm"><img src="{{ $u->avatar_url }}" alt="{{ $u->full_name }}"></div>
                  <div class="prof-user-meta">
                    <div class="prof-user-name">{{ $u->short_name ?: $u->full_name }}</div>
                    <div class="prof-user-handle">{{ '@' . $u->username }}</div>
                  </div>
                </a>
              @empty
                <div class="empty-state soft" data-type="followers" style="display:none;">
                  <i data-lucide="users"></i>
                  <p>No followers yet.</p>
                </div>
              @endforelse
            </div>
          </div>
        </div>
      </div>

      {{-- ─ Saved Tab (own profile only) ─ --}}
      @if($isOwn)
      <div class="tab-content hidden" id="tab-saved">
        <div class="panel">
          <div class="prof-section-header" style="padding: 10px 10px 0;">
            <div class="prof-section-title">
              <i data-lucide="bookmark"></i>
              <span>Saved Posts</span>
            </div>
          </div>
          <div class="prof-section-body" style="padding: 8px 10px 0;">
            @if(isset($savedPosts) && $savedPosts->count())
              <div id="savedPostsFeed">
                @foreach($savedPosts as $post)
                  @include('profile._post', ['post' => $post, 'isOwn' => $isOwn, 'me' => $me])
                @endforeach
              </div>
            @else
              <div class="empty-state">
                <i data-lucide="bookmark"></i>
                <p>No saved posts yet. Tap the bookmark on any post to save it here.</p>
              </div>
            @endif
          </div>
        </div>
      </div>
      @endif

      {{-- ─ Application Tab ─ --}}
      @if($isOwn && $profileUser->doctor_status !== 'none' && $profileUser->doctor_status !== null)
      <div class="tab-content hidden" id="tab-application">
        @include('profile._application')
      </div>
      @endif

    </main>
  </div>
</main>

{{-- Toast notification --}}
<div class="toast" id="toast"></div>

{{-- JS Config — routes and page state --}}
<script>
  window.PROFILE_USER_ID = {{ $profileUser->id }};
  window.IS_OWN_PROFILE  = {{ $isOwn ? 'true' : 'false' }};
  window.AUTH_USER_ID    = {{ $me ? $me->id : 'null' }};
  window.ROUTES = {
    updateInfo:    '{{ route('profile.update.info') }}',
    updatePhoto:   '{{ route('profile.update.photo') }}',
    deletePhoto:   '{{ route('profile.delete.photo') }}',
    updateCover:   '{{ route('profile.update.cover') }}',
    deleteCover:   '{{ route('profile.delete.cover') }}',
    storePost:     '{{ route('profile.posts.store') }}',
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
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/profile.js?v=' . time()) }}" defer></script>
@endpush
