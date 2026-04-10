@extends('layouts.dashboard')

@section('title', 'Post - AskDocPH')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/post.css') }}">
@endpush

@section('content')
@php
  $me = Auth::user();
  $backUrl = ($post->group_id && isset($group))
    ? route('groups.show', $group->id)
    : route('profile.show', $post->user_id);

  $backLabel = ($post->group_id && isset($group))
    ? 'Back to Group'
    : 'Back to Profile';

  $contextLabel = ($post->group_id && isset($group))
    ? ('Group: ' . $group->name)
    : ('Posted by @' . $post->user->username);
@endphp

<main class="dash single-post-page">

  <div class="dash-body single-post-body">
    <section class="dash-main single-post-main">
      <div class="panel single-post-header">
        <a class="back-link" href="{{ $backUrl }}">
          <i data-lucide="arrow-left"></i>
          <span>{{ $backLabel }}</span>
        </a>

        @if($post->group_id && $group)
          <a class="context-link" href="{{ route('groups.show', $group->id) }}">{{ $contextLabel }}</a>
        @else
          <a class="context-link" href="{{ route('profile.show', $post->user_id) }}">{{ $contextLabel }}</a>
        @endif
      </div>

      @if($canView)
        @include('profile._post', ['post' => $post, 'me' => $me, 'group' => $group])
      @else
        <div class="panel empty-state">
          <i data-lucide="lock"></i>
          <p>You must join this group to view this post.</p>
          @if($group)
            <a class="btn primary" href="{{ route('groups.show', $group->id) }}">Go to Group</a>
          @endif
        </div>
      @endif
    </section>
  </div>
</main>

<div class="toast" id="toast"></div>

<script>
  window.AUTH_USER_ID = {{ $me ? $me->id : 'null' }};
  window.ROUTES = {
    toggleLike: function(id){ return '/profile/posts/' + id + '/like'; },
    toggleSave: function(id){ return '/profile/posts/' + id + '/save'; },
    storeComment: function(id){ return '/profile/posts/' + id + '/comments'; },
    destroyComment: function(id){ return '/profile/comments/' + id; },
    updatePost: function(id){ return '/profile/posts/' + id; },
    destroyPost: function(id){ return '/profile/posts/' + id; },
    profileNetwork: function(id){ return '/api/profile/' + id + '/network'; },
  };
</script>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/profile.js?v=' . time()) }}" defer></script>
@endpush
