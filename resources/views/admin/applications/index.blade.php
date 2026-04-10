@extends('layouts.admin')

@section('title', 'Admin - Doctor Applications')

@push('styles')
<style>
/* Extend/Override default dashboard tokens for admin-specific pages */
.admin-container {
    width: 100%;
    margin: 0 auto;
    background: var(--panel);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); /* very soft shadow */
}
.admin-header {
    padding: 25px 30px;
}
.admin-header h1 {
    font-size: 1.6rem;
    font-weight: 600;
    margin: 0;
    color: var(--text);
}
.admin-filters {
    padding: 0 30px 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: flex-end;
}
.admin-search-box {
    flex: 1;
    min-width: 300px;
    position: relative;
    display: flex;
    align-items: center;
}
.admin-search-box i,
.admin-search-box svg {
    position: absolute !important;
    left: 15px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    width: 18px !important;
    height: 18px !important;
    color: var(--muted);
    pointer-events: none;
    z-index: 5;
}
.admin-search-box input {
    width: 100%;
    padding: 12px 15px 12px 42px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--input-bg);
    color: var(--text);
    font-size: 0.95rem;
    transition: all 0.2s;
}
.admin-search-box input:focus {
    border-color: var(--primary);
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
.admin-date-filters {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}
.date-input-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.date-input-group label {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text);
}
.date-input-group input[type="date"] {
    padding: 10px 15px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--input-bg);
    color: var(--text);
    font-family: inherit;
    font-size: 0.9rem;
    cursor: pointer;
}
/* Tabs */
.admin-tabs {
    display: flex;
    gap: 25px;
    padding: 0 30px;
    border-bottom: 1px solid var(--border);
}
.admin-tabs a {
    text-decoration: none;
    color: var(--text-muted);
    font-weight: 500;
    padding: 12px 10px;
    font-size: 0.95rem;
    transition: color 0.2s;
    border-bottom: 2px solid transparent;
}
.admin-tabs a:hover {
    color: var(--text);
}
.admin-tabs a.active {
    color: var(--primary);
    border-bottom: 2px solid var(--primary);
    font-weight: 600;
}
/* Table */
.table {
    width: 100%;
    border-collapse: collapse;
}
.table th, .table td {
    padding: 18px 30px;
    text-align: left;
    border-bottom: 1px solid var(--border);
    color: var(--text);
}
.table th {
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--muted);
    letter-spacing: 0.5px;
}
.table td {
    font-size: 0.95rem;
}
.table tr:hover {
    background: var(--hover);
}
/* Pills */
.badge {
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    letter-spacing: 0.5px;
}
.badge.pending {
    background: rgba(251, 191, 36, 0.15);
    color: #d97706;
    border: 1px solid rgba(251, 191, 36, 0.4);
}
.badge.approved {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.3);
}
.badge.rejected {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
    border: 1px solid rgba(239, 68, 68, 0.3);
}
/* Buttons */
.btn-sm {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 600;
    color: #ffffff;
    background: var(--primary, #3b82f6);
    border: none;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px -1px rgba(59, 130, 246, 0.1);
}
.btn-sm:hover {
    background: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.2);
}
.btn-sm i {
    width: 16px;
    height: 16px;
}
.table-footer {
    padding: 20px 30px;
    color: var(--muted);
    font-size: 0.9rem;
}
.admin-body {
    padding: 24px;
    max-width: 1200px;
    margin: 0 auto;
    width: 100%;
}
.admin-main {
    width: 100%;
}
</style>
@endpush

@section('content')

<main class="dash">

  <div class="admin-body">
    {{-- Admin Feed Area --}}
    <section class="admin-main">
      <div class="admin-container">
        
        <div class="admin-header">
            <h1>Doctor Applications Overview</h1>
        </div>

        <div class="admin-filters">
            <form action="{{ route('admin.applications.index') }}" method="GET" style="display: flex; gap: 20px; flex-wrap: wrap; width: 100%; align-items: flex-end;">
                <input type="hidden" name="tab" value="{{ $tab }}">
                
                <div class="admin-search-box">
                    <i data-lucide="search"></i>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search by name or email..." autocomplete="off">
                </div>

                <div class="admin-date-filters">
                    <div class="date-input-group">
                        <label>From Date</label>
                        <input type="date" name="from_date" value="{{ $fromDate ?? '' }}" onchange="this.form.submit()">
                    </div>
                    <div class="date-input-group">
                        <label>To Date</label>
                        <input type="date" name="to_date" value="{{ $toDate ?? '' }}" onchange="this.form.submit()">
                    </div>
                </div>
            </form>
        </div>

        {{-- Categories Navigation --}}
        <div class="admin-tabs">
            <a href="{{ route('admin.applications.index', ['tab' => 'all', 'search' => $search, 'from_date' => $fromDate, 'to_date' => $toDate]) }}" class="{{ $tab === 'all' ? 'active' : '' }}">
                All {{ $counts['all'] > 0 ? '('.$counts['all'].')' : '' }}
            </a>
            <a href="{{ route('admin.applications.index', ['tab' => 'pending', 'search' => $search, 'from_date' => $fromDate, 'to_date' => $toDate]) }}" class="{{ $tab === 'pending' ? 'active' : '' }}">
                Pending {{ $counts['pending'] > 0 ? '('.$counts['pending'].')' : '' }}
            </a>
            <a href="{{ route('admin.applications.index', ['tab' => 'approved', 'search' => $search, 'from_date' => $fromDate, 'to_date' => $toDate]) }}" class="{{ $tab === 'approved' ? 'active' : '' }}">
                Approved {{ $counts['approved'] > 0 ? '('.$counts['approved'].')' : '' }}
            </a>
            <a href="{{ route('admin.applications.index', ['tab' => 'rejected', 'search' => $search, 'from_date' => $fromDate, 'to_date' => $toDate]) }}" class="{{ $tab === 'rejected' ? 'active' : '' }}">
                Rejected {{ $counts['rejected'] > 0 ? '('.$counts['rejected'].')' : '' }}
            </a>
        </div>

        @if(session('success'))
            <div style="padding: 15px; background: rgba(46, 204, 113, 0.1); color: #2ecc71; border: 1px solid rgba(46, 204, 113, 0.3); border-radius: 8px; margin-bottom: 20px;">
                {{ session('success') }}
            </div>
        @endif

        @if($applications->isEmpty())
            <p style="text-align: center; color: var(--text-muted); margin-top: 40px;">No doctor applications found.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Submitted At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applications as $app)
                        <tr>
                            <td style="font-weight: 600;">#{{ $app->id }}</td>
                            <td>{{ $app->user->fname }} {{ $app->user->lname }}</td>
                            <td style="color: var(--muted);">{{ $app->user->email }}</td>
                            <td>
                                <span class="badge {{ strtolower($app->status) }}">
                                    {{ $app->status }}
                                </span>
                            </td>
                            <td style="color: var(--muted); font-size: 0.85rem;">
                                {{ $app->submitted_at->format('M d, Y') }}<br>
                                {{ $app->submitted_at->format('h:i A') }}
                            </td>
                            <td>
                                <a href="{{ route('admin.applications.show', ['id' => $app->id, 'tab' => $tab, 'search' => $search, 'from_date' => $fromDate, 'to_date' => $toDate]) }}" class="btn-sm">
                                    <i data-lucide="eye"></i> View Details
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            {{-- Table Footer Summary --}}
            <div class="table-footer">
                Showing {{ $applications->count() }} of {{ $applications->count() }} applications
            </div>
        @endif
      </div>
    </section>

  </div>

</main>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Debounced Search Submission
    const searchInput = document.querySelector('input[name="search"]');
    const filterForm = searchInput ? searchInput.closest('form') : null;
    let searchTimeout;

    if (searchInput && filterForm) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Submit the form
                filterForm.submit();
            }, 500); // 500ms delay
        });

        // Focus search input and put cursor at end on return if search query exists
        if (searchInput.value) {
            searchInput.focus();
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        }
    }
});
</script>
@endpush
@endsection
