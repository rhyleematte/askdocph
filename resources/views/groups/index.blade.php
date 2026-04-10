@extends('layouts.dashboard')

@section('title', 'Support Groups – AskDocPH')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/groups.css') }}">
@endpush

@section('content')
@php
  $me = Auth::user();
@endphp

<main class="dash dashboard-groups-index">
  <div class="dash-body">
    @include('partials.sidebar', ['active' => 'groups'])

    {{-- ══ CENTER – main content ══ --}}
    <main class="groups-main">
      <div class="groups-header-panel">
        <div class="groups-header-left">
          <h1>Support Groups</h1>
          <p>Connect with others who understand your journey. Join supportive communities focused on healing and growth.</p>
        </div>
        @if($me->doctor_status === 'approved')
        <div class="groups-header-right">
          <a href="{{ route('groups.create') }}" class="create-group-btn" style="text-decoration: none;">
            <i data-lucide="plus"></i> Create Group
          </a>
        </div>
        @endif
      </div>

      <div class="groups-toolbar">
        <div class="groups-search-wrap">
          <i data-lucide="search"></i>
          <input type="text" id="groupHeaderSearch" placeholder="Search groups..." autocomplete="off">
        </div>
        <div class="groups-filter-wrap">
          <select id="groupSortSelect">
            <option value="newest" {{ ($sort ?? 'newest') === 'newest' ? 'selected' : '' }}>Newest</option>
            <option value="oldest" {{ ($sort ?? 'newest') === 'oldest' ? 'selected' : '' }}>Oldest</option>
            <option value="members_desc" {{ ($sort ?? 'newest') === 'members_desc' ? 'selected' : '' }}>Highest Members</option>
            <option value="members_asc" {{ ($sort ?? 'newest') === 'members_asc' ? 'selected' : '' }}>Lowest Members</option>
            <option value="active_desc" {{ ($sort ?? 'newest') === 'active_desc' ? 'selected' : '' }}>Most Active</option>
            <option value="active_asc" {{ ($sort ?? 'newest') === 'active_asc' ? 'selected' : '' }}>Least Active</option>
          </select>
        </div>
      </div>

      <div class="groups-grid">
        @foreach($groups as $group)
        @php
            $isJoined = in_array($group->id, $myGroupIds);
        @endphp
        <div class="group-card">
          <div class="group-cover" style="background-image: url('{{ $group->cover_url }}'); background-size: cover; background-position: center;">
          </div>
          <div class="group-info">
            <h2 class="group-title">{{ $group->name }}</h2>
            <p class="group-desc">{{ Str::limit($group->description, 100) }}</p>
            
            <div class="group-stats">
              <div class="group-stats-item">
                <i data-lucide="users"></i> {{ number_format($group->members_count) }} members
              </div>
              <div class="group-stats-item group-active-stat">
                <i data-lucide="trending-up"></i> {{ $group->activity_level }}
              </div>
            </div>

            @if($isJoined)
              <a href="{{ route('groups.show', $group->id) }}" class="group-btn joined">
                <i data-lucide="eye"></i> View
              </a>
            @else
              <button class="group-btn join" onclick="joinGroup({{ $group->id }})">
                Join Group
              </button>
            @endif
          </div>
        </div>
        @endforeach
      </div>
    </main>
  </div>
</main>
@endsection

@push('scripts')
<script>
async function joinGroup(id) {
    try {
        let res = await fetch(`/groups/${id}/join`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        });
        let data = await res.json();
        if(data.ok) location.reload();
    } catch(e) {}
}

// Simple header search filter for groups list
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('groupHeaderSearch');
    const cards = Array.from(document.querySelectorAll('.groups-grid .group-card'));

    if (!searchInput || !cards.length) return;

    const norm = (s) => (s || '').toString().trim().toLowerCase();

    function apply() {
        const q = norm(searchInput.value);
        cards.forEach(card => {
            const titleEl = card.querySelector('.group-title');
            const descEl = card.querySelector('.group-desc');
            const hay = norm((titleEl?.textContent || '') + ' ' + (descEl?.textContent || ''));
            const show = !q || hay.indexOf(q) !== -1;
            card.style.display = show ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', apply);
});

// Sort selector
document.addEventListener('DOMContentLoaded', () => {
    const sortSelect = document.getElementById('groupSortSelect');
    if (!sortSelect) return;

    sortSelect.addEventListener('change', () => {
        const url = new URL(window.location.href);
        url.searchParams.set('sort', sortSelect.value);
        window.location.href = url.toString();
    });
});
</script>
@endpush
