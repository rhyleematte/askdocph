@php
  $liked     = $me ? $post->isLikedBy($me->id) : false;
  $isSaved   = $me ? $post->isSavedBy($me->id) : false;
  $isOwner   = $me && $post->user_id === $me->id;
  $isGroupCreator = isset($group) && $me && $group->creator_id === $me->id;
  $canManage = $isOwner || $isGroupCreator;
  $canEdit   = $isOwner;
@endphp

<article class="panel post" data-post-id="{{ $post->id }}">
  {{-- Post header --}}
  <div class="post-head">
    <div class="avatar md">
      <img src="{{ $post->user->avatar_url }}" alt="{{ $post->user->full_name }}">
    </div>
    @php
      $isDoctor = $post->user && $post->user->role === 'doctor' && $post->user->doctor_status === 'approved';
      $verifiedBadge = $isDoctor ? '<i data-lucide="badge-check" class="doctor-badge" title="Verified Doctor"></i>' : '';
      $profTitleHtml = ($isDoctor && $post->user->professional_titles && trim($post->user->professional_titles)) 
        ? '<div class="prof-title">' . e(trim($post->user->professional_titles)) . '</div>' 
        : '';
    @endphp
    <div class="post-meta">
      <div class="post-name-row">
        <span class="post-name">{{ $post->user->full_name }}</span>
        <span class="post-handle">{{ '@' . $post->user->username }}</span>
        {!! $verifiedBadge !!}
      </div>
      {!! $profTitleHtml !!}
      <div class="post-sub">{{ $post->created_at->diffForHumans() }}</div>
    </div>
    @if($canManage)
    <div class="post-menu-wrap">
      <button class="icon-btn post-menu-btn" type="button" title="Options">
        <i data-lucide="more-horizontal"></i>
      </button>
      <div class="post-menu hidden">
        @if($canEdit)
        <button class="post-menu-item edit-post-btn" type="button"
            data-post-id="{{ $post->id }}"
            data-text="{{ $post->text_content ?? '' }}"
            data-media="{{ json_encode($post->media->map(function($m) { return ['id' => $m->id, 'url' => asset('storage/' . $m->path), 'media_type' => $m->media_type]; })) }}">
          <i data-lucide="pencil"></i> Edit
        </button>
        @endif
        <button class="post-menu-item delete-post-btn danger" type="button"
            data-post-id="{{ $post->id }}">
          <i data-lucide="trash-2"></i> Delete
        </button>
      </div>
    </div>
    @endif
  </div>

  {{-- Post body --}}
  @if($post->text_content)
  <div class="post-body post-text-content js-collapsible">{!! preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank" rel="noopener noreferrer" class="post-link" style="color:var(--brand);text-decoration:underline;">$1</a>', htmlspecialchars($post->text_content)) !!}</div>
  @endif

  @if(!empty($post->hashtags_array))
    <div class="post-tags">
      @foreach($post->hashtags_array as $tag)
        <span class="tag">#{{ $tag }}</span>
      @endforeach
    </div>
  @endif

  {{-- Resource card (resource_share) --}}
  @if($post->resource)
    <a href="{{ route('resources.show', $post->resource->id) }}" class="post-resource-card" style="display:flex; gap:12px; border:1px solid var(--border); border-radius:14px; padding:12px; text-decoration:none; color:inherit; margin-bottom:12px;">
      <div class="res-mini-thumb" style="width:64px; height:64px; border-radius:12px; overflow:hidden; flex-shrink:0; background:var(--hover); border:1px solid var(--border);">
        <img src="{{ $post->resource->thumbnail_url }}" alt="{{ $post->resource->title }}" style="width:100%; height:100%; object-fit:cover;">
      </div>
      <div class="res-mini-info" style="min-width:0; display:flex; flex-direction:column; gap:4px;">
        <div class="res-mini-type" style="font-size:11px; font-weight:800; color:var(--muted); text-transform:uppercase;">{{ $post->resource->type }}</div>
        <div class="res-mini-title" style="font-weight:900; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $post->resource->title }}</div>
        <div class="res-mini-desc" style="font-size:13px; color:var(--muted); overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">{{ $post->resource->description }}</div>
      </div>
    </a>
  @endif

@php
/* Group share block removed as requested */
@endphp

  {{-- Shared post card (post_share) --}}
  @if($post->sharedPost)
    <div class="shared-post-card">
      <div class="post-head">
        <div class="avatar md">
          <img src="{{ $post->sharedPost->user->avatar_url }}" alt="{{ $post->sharedPost->user->full_name }}">
        </div>
        @php
          $sharedUser = $post->sharedPost->user;
        @endphp
        <div class="post-meta">
          @php
            $isSharedDoctor = $sharedUser && $sharedUser->doctor_status === 'approved' && (!$sharedUser->role || $sharedUser->role === 'doctor');
            $spVerifiedBadge = $isSharedDoctor ? '<i data-lucide="badge-check" class="doctor-badge" title="Verified Doctor"></i>' : '';
            $spProfTitleHtml = ($isSharedDoctor && $sharedUser->professional_titles && trim($sharedUser->professional_titles))
              ? '<div class="prof-title">' . e(trim($sharedUser->professional_titles)) . '</div>'
              : '';
          @endphp
          <div class="post-name-row">
            <span class="post-name">{{ $sharedUser->full_name }}</span>
            <span class="post-handle">{{ '@' . $sharedUser->username }}</span>
            {!! $spVerifiedBadge !!}
          </div>
          {!! $spProfTitleHtml !!}
          <div class="post-sub">{{ $post->sharedPost->created_at->diffForHumans() }}</div>
        </div>
      </div>
      @if($post->sharedPost->text_content)
        <div class="post-body js-collapsible">{!! preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank" rel="noopener noreferrer" class="post-link" style="color:var(--brand);text-decoration:underline;">$1</a>', htmlspecialchars($post->sharedPost->text_content)) !!}</div>
      @endif
      @if($post->sharedPost->resource)
        <a href="{{ route('resources.show', $post->sharedPost->resource->id) }}" class="post-resource-card" style="display:flex; gap:12px; border:1px solid var(--border); border-radius:14px; padding:12px; text-decoration:none; color:inherit; margin-top:10px;">
          <div class="res-mini-thumb" style="width:64px; height:64px; border-radius:12px; overflow:hidden; flex-shrink:0; background:var(--hover); border:1px solid var(--border);">
            <img src="{{ $post->sharedPost->resource->thumbnail_url }}" alt="{{ $post->sharedPost->resource->title }}" style="width:100%; height:100%; object-fit:cover;">
          </div>
          <div class="res-mini-info" style="min-width:0; display:flex; flex-direction:column; gap:4px;">
            <div class="res-mini-type" style="font-size:11px; font-weight:800; color:var(--muted); text-transform:uppercase;">{{ $post->sharedPost->resource->type }}</div>
            <div class="res-mini-title" style="font-weight:900; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $post->sharedPost->resource->title }}</div>
            <div class="res-mini-desc" style="font-size:13px; color:var(--muted); overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">{{ $post->sharedPost->resource->description }}</div>
          </div>
        </a>
      @endif
      @if($post->sharedPost->media && $post->sharedPost->media->isNotEmpty())
        <div class="post-media-grid shared-post-media-grid media-count-{{ min($post->sharedPost->media->count(), 4) }}"
             data-media="{{ json_encode($post->sharedPost->media->map(function($m) { return ['id' => $m->id, 'url' => asset('storage/' . $m->path), 'media_type' => $m->media_type]; })->values()) }}">
          @foreach($post->sharedPost->media->take(4) as $m)
            @if($m->media_type === 'video')
              <video src="{{ asset('storage/' . $m->path) }}" controls class="post-media-item"></video>
            @else
              <img src="{{ asset('storage/' . $m->path) }}" alt="Shared media" class="post-media-item">
            @endif
          @endforeach
          @if($post->sharedPost->media->count() > 4)
            <div class="media-more">+{{ $post->sharedPost->media->count() - 4 }}</div>
          @endif
        </div>
      @endif
    </div>
  @endif

  {{-- Media grid --}}
  @if($post->media->isNotEmpty())
  <div class="post-media-grid media-count-{{ min($post->media->count(), 4) }}"
       data-media="{{ json_encode($post->media->map(function($m) { return ['id' => $m->id, 'url' => asset('storage/' . $m->path), 'media_type' => $m->media_type]; })) }}">
    @foreach($post->media->take(4) as $media)
      @if($media->media_type === 'video')
        <video src="{{ asset('storage/' . $media->path) }}" controls class="post-media-item"></video>
      @else
        <img src="{{ asset('storage/' . $media->path) }}" alt="Post image" class="post-media-item">
      @endif
    @endforeach
    @if($post->media->count() > 4)
      <div class="media-more">+{{ $post->media->count() - 4 }}</div>
    @endif
  </div>
  @endif

  {{-- Actions --}}
  <div class="post-actions">
    <button class="post-btn like-btn {{ $liked ? 'liked' : '' }}" type="button"
        data-post-id="{{ $post->id }}">
      <i data-lucide="{{ $liked ? 'heart' : 'heart' }}" class="like-icon"></i>
      <span class="like-count">{{ $post->likes->count() }}</span>
    </button>

    <button class="post-btn comment-toggle-btn" type="button" data-post-id="{{ $post->id }}">
      <i data-lucide="message-square"></i>
      <span class="comment-count">{{ $post->allComments()->count() }}</span>
    </button>

    <button class="post-btn save-btn {{ $isSaved ? 'saved' : '' }} end" type="button" title="Save" data-post-id="{{ $post->id }}" data-saved="{{ $isSaved ? '1' : '0' }}">
      <i data-lucide="bookmark"></i>
    </button>

    <button class="post-btn js-share-post" type="button" title="Share"
            data-post-id="{{ $post->id }}"
            data-preview="{{ $post->text_content ? \Illuminate\Support\Str::limit($post->text_content, 80) : 'a post' }}">
      <i data-lucide="share-2"></i>
    </button>
  </div>

  {{-- Comments section --}}
  <div class="comments-section hidden" id="comments-{{ $post->id }}">
    {{-- Comment input --}}
    @auth
    <div class="comment-composer">
      <div class="avatar sm">
        <img src="{{ Auth::user()->avatar_url }}" alt="You">
      </div>
      <div class="comment-input-wrap">
        <input type="text"
               class="comment-input"
               placeholder="Write a comment…"
               data-post-id="{{ $post->id }}">
        <button class="comment-send-btn" type="button" data-post-id="{{ $post->id }}">
          <i data-lucide="send"></i>
        </button>
      </div>
    </div>
    @endauth

    {{-- Existing comments --}}
    <div class="comments-list" id="comments-list-{{ $post->id }}">
      @foreach($post->comments as $comment)
        @include('profile._comment', ['comment' => $comment, 'me' => $me, 'post' => $post])
      @endforeach
    </div>
  </div>
</article>
