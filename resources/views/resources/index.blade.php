@extends('layouts.dashboard')

@section('title', 'Resources – AskDocPH')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/resources.css') }}?v={{ time() }}">
@endpush

@section('content')
  @php
    $me = Auth::user();
  @endphp

  <main class="dash">
    <div class="dash-body">
      @include('partials.sidebar', ['active' => 'resources'])

      <main class="res-main">
        <div class="res-header-panel">
          <div class="res-header-left">
            <h1>Mental Health Resources</h1>
            <p>Find professional tools, articles, and media to support your wellness journey.</p>
          </div>
          @if(Auth::check() && Auth::user()->can('create', 'App\Models\Resource'))
            <div class="res-header-right">
              <a href="{{ route('resources.create') }}" class="create-res-btn">
                <i data-lucide="plus"></i> Create Resource
              </a>
            </div>
          @endif
        </div>

        {{-- Search + Filters --}}
        <div class="res-filterbar panel">
          <div class="res-search">
            <i data-lucide="search"></i>
            <input id="resSearchInput" type="text" placeholder="Search resources..." autocomplete="off">
          </div>
          <div class="res-filters" id="resFilters">
            <button class="chip-btn active" type="button" data-filter="all">All</button>
            <button class="chip-btn" type="button" data-filter="Article">Articles</button>
            <button class="chip-btn" type="button" data-filter="Audio">Audio</button>
            <button class="chip-btn" type="button" data-filter="Video">Videos</button>
            <button class="chip-btn" type="button" data-filter="Workbook">Workbooks</button>
          </div>
        </div>

        <div class="res-grid">
          @if($resources->count() > 0)
            @foreach($resources as $res)
              <div class="res-card" data-type="{{ $res->type }}" data-title="{{ strtolower($res->title ?? '') }}"
                data-desc="{{ strtolower($res->description ?? '') }}" data-tags="{{ strtolower($res->hashtags ?? '') }}">
                <div class="res-card-thumb">
                  <img src="{{ $res->thumbnail_url }}" alt="{{ $res->title }}">
                  <span class="res-card-type">{{ $res->type }}</span>
                </div>
                <div class="res-card-content">
                  <h3 class="res-card-title">{{ \Illuminate\Support\Str::limit($res->title, 50) }}</h3>
                  <p class="res-card-desc">{{ \Illuminate\Support\Str::limit($res->description, 100) }}</p>

                  @php
                    $tags = $res->hashtags_array ?? [];
                    $tags = array_values(array_filter(array_map(function ($t) {
                      $t = ltrim(trim((string) $t), '#');
                      return $t === '' ? null : $t;
                    }, $tags)));
                  @endphp
                  @if(count($tags))
                    <div class="res-card-tags" aria-label="Hashtags">
                      @foreach(array_slice($tags, 0, 4) as $tag)
                        <span class="res-card-tag">#{{ $tag }}</span>
                      @endforeach
                      @if(count($tags) > 4)
                        <span class="res-card-tag res-card-tag-more">+{{ count($tags) - 4 }}</span>
                      @endif
                    </div>
                  @endif

                  <div class="res-card-footer">
                    <div class="res-card-meta">
                      <div class="res-meta-item">
                        <i data-lucide="user"></i>
                        <span>{{ $res->user->short_name ?: $res->user->full_name }}</span>
                      </div>
                      @if($res->duration_meta)
                        <div class="res-meta-item">
                          <i data-lucide="clock"></i>
                          <span>{{ $res->duration_meta }}</span>
                        </div>
                      @endif
                    </div>
                    <div>
                      @auth
                        @php
                          $isJoined = in_array($res->id, $joinedResourceIds ?? []);
                        @endphp
                        <a href="{{ route('resources.show', $res->id) }}" class="res-card-btn">
                          {{ $isJoined ? 'Joined' : 'View More' }}
                        </a>
                      @endauth

                      @guest
                        <a href="{{ route('resources.show', $res->id) }}" class="res-card-btn">View More</a>
                      @endguest
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          @else
            <div class="res-empty" style="grid-column: 1 / -1;">
              <i data-lucide="book-copy"></i>
              <p>No resources found yet. Check back soon!</p>
            </div>
          @endif
        </div>

        <div class="res-empty hidden" id="resNoResults" style="grid-column: 1 / -1; margin-top: 16px;">
          <i data-lucide="search-x"></i>
          <p>No matching resources.</p>
        </div>
      </main>
    </div>
  </main>
@endsection

@push('scripts')
  <script>
        document.addEventListener('DOMContentLoaded', function (    ) {
        var search = document.getElementById('resSearchInput');
        var filters = document.getElementById('resFilters');
        var cards = Array.from(document.querySelectorAll('.res-grid .res-card'));
        var noResults = document.getElementById('resNoResults');

        function norm(s) { return (s || '').toString().trim().toLowerCase(); }
        var state = { q: '', type: 'all' };

        function apply() {
          var shown = 0;
          cards.forEach(function (card) {
            var type = card.dataset.type || '';
            var hay = (card.dataset.title || '') + ' ' + (card.dataset.desc || '') + ' ' + (card.dataset.tags || '') + ' ' + norm(type);
            var okType = (state.type === 'all') || (type === state.type);
            var okQ = !state.q || hay.indexOf(state.q) !== -1;
            var show = okType && okQ;
            card.style.display = show ? '' : 'none';
            if (show) shown++;
          });

          if (noResults) noResults.classList.toggle('hidden', shown !== 0 || cards.length === 0);
          if (window.lucide) lucide.createIcons();
        }

        if (search) {
          search.addEventListener('input', function () {
            state.q = norm(search.value);
            apply();
          });
        }

        if (filters) {
          filters.addEventListener('click', function (e) {
            var btn = e.target.closest('button[data-filter]');
            if (!btn) return;
            state.type = btn.dataset.filter || 'all';
            filters.querySelectorAll('button[data-filter]').forEach(function (b) {
              b.classList.toggle('active', b === btn);
            });
            apply();
          });
        }
      });
    </script>
@endpush