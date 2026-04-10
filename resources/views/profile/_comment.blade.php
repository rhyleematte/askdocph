<div class="comment-item" id="comment-{{ $comment->id }}">
  <div class="avatar sm">
    <img src="{{ $comment->user->avatar_url }}" alt="{{ $comment->user->full_name }}">
  </div>
  <div class="comment-bubble">
    <div class="comment-meta">
      <span class="comment-author">{{ $comment->user->full_name }}</span>
      <span class="comment-time">{{ $comment->created_at->diffForHumans() }}</span>
      @if($me && $comment->user_id === $me->id)
      <button class="comment-delete-btn" type="button" data-comment-id="{{ $comment->id }}" title="Delete">
        <i data-lucide="x"></i>
      </button>
      @endif
    </div>
    <p class="comment-text">{!! preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank" rel="noopener noreferrer" class="post-link" style="color:var(--brand);text-decoration:underline;">$1</a>', htmlspecialchars($comment->comment_text)) !!}</p>
    @auth
    <button class="reply-toggle-btn" type="button" data-comment-id="{{ $comment->id }}" data-post-id="{{ $post->id }}">
      Reply
    </button>
    @endauth

    {{-- Reply composer (hidden by default) --}}
    @auth
    <div class="reply-composer hidden" id="reply-composer-{{ $comment->id }}">
      <input type="text" class="comment-input reply-input" placeholder="Write a reply…"
             data-post-id="{{ $post->id }}" data-parent-id="{{ $comment->id }}">
      <button class="comment-send-btn reply-send-btn" type="button"
              data-post-id="{{ $post->id }}" data-parent-id="{{ $comment->id }}">
        <i data-lucide="send"></i>
      </button>
    </div>
    @endauth

    {{-- Replies --}}
    @if($comment->replies && $comment->replies->isNotEmpty())
    <div class="replies-list" id="replies-{{ $comment->id }}">
      @foreach($comment->replies as $reply)
        <div class="comment-item reply-item" id="comment-{{ $reply->id }}">
          <div class="avatar sm">
            <img src="{{ $reply->user->avatar_url }}" alt="{{ $reply->user->full_name }}">
          </div>
          <div class="comment-bubble">
            <div class="comment-meta">
              <span class="comment-author">{{ $reply->user->full_name }}</span>
              <span class="comment-time">{{ $reply->created_at->diffForHumans() }}</span>
              @if($me && $reply->user_id === $me->id)
              <button class="comment-delete-btn" type="button" data-comment-id="{{ $reply->id }}" title="Delete">
                <i data-lucide="x"></i>
              </button>
              @endif
            </div>
            <p class="comment-text">{!! preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank" rel="noopener noreferrer" class="post-link" style="color:var(--brand);text-decoration:underline;">$1</a>', htmlspecialchars($reply->comment_text)) !!}</p>
            @auth
            <button class="reply-toggle-btn" type="button" data-comment-id="{{ $comment->id }}" data-post-id="{{ $post->id }}" data-reply-to="{{ $reply->user->username }}">
              Reply
            </button>
            @endauth
          </div>
        </div>
      @endforeach
    </div>
    @else
    <div class="replies-list" id="replies-{{ $comment->id }}"></div>
    @endif
  </div>
</div>
