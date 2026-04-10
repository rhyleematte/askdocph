@extends('layouts.dashboard')

@section('title', 'Dashboard - AskDocPH')

@section('content')
@php
  $user      = Auth::user();
  $avatarUrl = $user->avatar_url;
  $fullName  = $user->full_name ?: ($user->name ?? 'User');
  $shortName = $user->short_name ?: $fullName;
  $username  = $user->username ?? 'username';
@endphp

{{-- Inject JS routes --}}
<script>
window.DASH_ROUTES = {
  feed:          "{{ route('dashboard.feed') }}",
  storePost:     "{{ route('profile.posts.store') }}",
  toggleLike:    function(id){ return "/profile/posts/" + id + "/like"; },
  toggleSave:    function(id){ return "/profile/posts/" + id + "/save"; },
  storeComment:  function(id){ return "/profile/posts/" + id + "/comments"; },
  destroyComment:function(id){ return "/profile/comments/" + id; },
  destroyPost:   function(id){ return "/profile/posts/" + id; },
  updatePost:    function(id){ return "/profile/posts/" + id; },
};
window.MY_PROFILE_URL = "{{ route('profile.show', Auth::id()) }}";
</script>

<main class="dash">

  {{-- Body --}}
  <div class="dash-body">

    {{-- Left sidebar --}}
    @include('partials.sidebar', ['active' => 'feed'])

    @if(Auth::user()->isApprovedDoctor())
    @endif

    {{-- Main feed --}}
    <section class="dash-main">

      {{-- ── Post Composer ── --}}
      <div class="panel composer" id="composerPanel">
        <div class="composer-top">
          <div class="avatar sm">
            <img src="{{ $avatarUrl }}" alt="You" />
          </div>
          <textarea id="dashPostText" placeholder="Share your thoughts, feelings, or progress..."></textarea>
        </div>

        {{-- Media preview --}}
        <div id="mediaPreviewArea" class="media-preview-grid" style="display:none;"></div>

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
          {{-- Photo button --}}
          <label class="chip-btn" for="mediaUpload" title="Attach photo/video" style="cursor:pointer;">
            <i data-lucide="image"></i> Photo
          </label>
          <input type="file" id="mediaUpload" accept="image/*,video/*" multiple style="display:none;" />

          {{-- Mood button --}}
          <button class="chip-btn" type="button" id="moodToggleBtn" title="Add mood">
            <i data-lucide="smile"></i> Mood
          </button>

          {{-- Hashtag button --}}
          <button class="chip-btn" type="button" id="hashtagToggleBtn" title="Add hashtags">
            <i data-lucide="hash"></i> Tags
          </button>

          {{-- Link button & popup --}}
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

      {{-- Feed --}}
      <div id="dashFeed">
        <div class="feed-loading panel" id="feedLoading">
          <i data-lucide="loader"></i>
          <span>Loading posts…</span>
        </div>
      </div>

      <div id="feedEmpty" class="empty-state panel" style="display:none;">
        <i data-lucide="file-text"></i>
        <p>No posts yet. Be the first to share something!</p>
      </div>

    </section>
  </div>
</main>

{{-- Toast --}}
<div id="dash-toast" class="dash-toast" aria-live="polite"></div>

@include('partials.ai_chat_modal')

@endsection
