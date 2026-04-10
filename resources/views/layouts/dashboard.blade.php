<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'AskDocPH')</title>
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap">

  <!-- Base + Dashboard CSS -->
  <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/messenger.css') }}">
  @if(Auth::check() && Auth::user()->isApprovedDoctor())
    <link rel="stylesheet" href="{{ asset('assets/css/schedule.css') }}">
  @endif

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>

  <!-- Page-specific styles -->
  @stack('styles')
</head>

<body>

@auth
  @php
    $layout_user = Auth::user();
    $layout_avatarUrl = $layout_user->avatar_url;
    $layout_fullName  = $layout_user->full_name ?: ($layout_user->name ?? 'User');
  @endphp
  <script>
    window.MY_ID = {{ Auth::id() }};
    window.MY_AVATAR = "{{ $layout_avatarUrl }}";
    window.MY_NAME = "{{ addslashes($layout_fullName) }}";
  </script>
  @include('partials.header')
  @include('partials.messenger')
  @include('partials.appointments_modal')
@endauth

@yield('content')


<!-- Share modal (used by Dashboard/Profile/Resources) -->
<div class="modal-backdrop share-modal" id="shareModal" aria-hidden="true">
  <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="shareModalTitle">
    <div class="modal-header">
      <h2 id="shareModalTitle">Share</h2>
      <button class="modal-close" id="shareModalCloseBtn" type="button" aria-label="Close">
        <i data-lucide="x"></i>
      </button>
    </div>
    <div class="share-modal-body">
      <label class="share-modal-label" for="shareModalText">Add a comment (optional)</label>
      <textarea id="shareModalText" class="share-modal-textarea" rows="4" placeholder="Say something about this..."></textarea>
      <div class="share-modal-preview" id="shareModalPreview" style="display:none;"></div>
      <div class="share-modal-actions">
        <button type="button" class="btn-cancel" id="shareModalCancelBtn">Cancel</button>
        <button type="button" class="btn-save" id="shareModalShareBtn">
          Share <i data-lucide="send"></i>
        </button>
      </div>
      <div class="form-feedback" id="shareModalFeedback"></div>
    </div>
  </div>
</div>

<!-- Dashboard JS -->
<script src="{{ asset('assets/js/mentions.js') }}?v={{ filemtime(public_path('assets/js/mentions.js')) }}" defer></script>
<script src="{{ asset('assets/js/notifications.js') }}?v={{ filemtime(public_path('assets/js/notifications.js')) }}" defer></script>
<script src="{{ asset('assets/js/dashboard.js') }}?v={{ filemtime(public_path('assets/js/dashboard.js')) }}" defer></script>
<script src="{{ asset('assets/js/post-ui.js') }}?v={{ filemtime(public_path('assets/js/post-ui.js')) }}" defer></script>
<script src="{{ asset('assets/js/messenger.js') }}" defer></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
<script src="{{ asset('assets/js/appointments.js') }}" defer></script>

@stack('scripts')
</body>
</html>
