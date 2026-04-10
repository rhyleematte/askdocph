@extends('layouts.admin')

@section('title', 'Admin - Application Details')

@push('styles')
<style>
.admin-container {
    width: 100%;
    margin: 0 auto;
    background: var(--panel);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}
.header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 25px 30px;
    border-bottom: 1px solid var(--border);
}
.header-top h1 {
    font-size: 1.6rem;
    font-weight: 600;
    margin: 0;
    color: var(--text);
}
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

.details-wrapper {
    padding: 30px;
}

.section-title {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 20px;
    display: block;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}
.detail-item strong {
    display: block;
    color: var(--muted);
    font-size: 0.75rem;
    margin-bottom: 5px;
    text-transform: uppercase;
    font-weight: 700;
}
.detail-item span {
    font-size: 1rem;
    color: var(--text);
    font-weight: 500;
}

.document-list {
    margin-top: 20px;
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
}
.document-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid var(--border);
    background: var(--panel);
}
.document-item:last-child {
    border-bottom: none;
}
.doc-name {
    font-weight: 600;
    color: var(--text);
    margin-bottom: 4px;
}
.doc-desc {
    font-size: 0.85rem;
    color: var(--muted);
}
.btn-outline {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--primary);
    background: transparent;
    border: 1px solid rgba(59, 130, 246, 0.4);
    transition: all 0.2s ease;
}
.btn-outline:hover {
    background: rgba(59, 130, 246, 0.05);
    border-color: var(--primary);
}

.review-actions {
    margin-top: 40px;
    padding-top: 30px;
    border-top: 1px solid var(--border);
}
.review-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}
.review-card {
    background: var(--bg);
    padding: 25px;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
}
.review-card textarea {
    width: 100%;
    min-height: 100px;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--input-bg);
    color: var(--text);
    font-family: inherit;
    font-size: 0.95rem;
    margin-bottom: 15px;
    resize: vertical;
}
.btn-solid {
    padding: 12px 24px;
    border-radius: 8px;
    border: none;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    color: white;
    transition: opacity 0.2s;
}
.btn-approve { background: #059669; }
.btn-reject { background: #dc2626; }
.btn-solid:hover { opacity: 0.9; }

.notes-box {
    background: var(--bg);
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid var(--primary);
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--muted);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 20px;
}
.back-link:hover {
    color: var(--primary);
}
/* Search & Filter Styles */
.admin-filters {
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
.admin-tabs {
    display: flex;
    gap: 25px;
    border-bottom: 1px solid var(--border);
}
.admin-tabs a {
    text-decoration: none;
    color: var(--muted);
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
    <section class="admin-main">
        
        <div class="admin-filters" style="padding: 0 0 20px 0;">
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
        <div class="admin-tabs" style="padding: 0 0 20px 0; margin-bottom: 20px;">
            <a href="{{ route('admin.applications.index', ['tab' => 'all', 'search' => $search, 'from_date' => $fromDate, 'to_date' => $toDate]) }}" class="{{ $tab === 'all' ? 'active' : '' }}">All</a>
            <a href="{{ route('admin.applications.index', ['tab' => 'pending', 'search' => $search, 'from_date' => $fromDate, 'to_date' => $toDate]) }}" class="{{ $tab === 'pending' ? 'active' : '' }}">Pending</a>
            <a href="{{ route('admin.applications.index', ['tab' => 'approved', 'search' => $search, 'from_date' => $fromDate, 'to_date' => $toDate]) }}" class="{{ $tab === 'approved' ? 'active' : '' }}">Approved</a>
            <a href="{{ route('admin.applications.index', ['tab' => 'rejected', 'search' => $search, 'from_date' => $fromDate, 'to_date' => $toDate]) }}" class="{{ $tab === 'rejected' ? 'active' : '' }}">Rejected</a>
        </div>

        <div class="admin-container">
            <div class="header-top">
                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <span style="font-size: 0.8rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 1px;">Doctor Application Review</span>
                    <h1>Application #{{ $application->id }}</h1>
                </div>
                <span class="badge {{ $application->status }}">{{ $application->status }}</span>
            </div>

            <div class="details-wrapper">

                {{-- === SECTION 1: PERSONAL INFORMATION === --}}
                <span class="section-title">1. Personal Information</span>
                @php
                    $bday = $application->user->bday ? \Carbon\Carbon::parse($application->user->bday) : null;
                    $age   = $bday ? $bday->age : null;
                    $genderMap = ['male' => 'Male', 'female' => 'Female', 'other' => 'Other', 'prefer_not_say' => 'Prefer not to say'];
                @endphp
                <div class="detail-grid" style="grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); margin-bottom: 30px;">
                    <div class="detail-item">
                        <strong>First Name</strong>
                        <span>{{ $application->user->fname ?? '—' }}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Middle Name</strong>
                        <span>{{ $application->user->mname ?? '—' }}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Last Name</strong>
                        <span>{{ $application->user->lname ?? '—' }}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Gender</strong>
                        <span>{{ $genderMap[$application->user->gender] ?? ucfirst($application->user->gender ?? '—') }}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Birthday</strong>
                        <span>{{ $bday ? $bday->format('M d, Y') : '—' }}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Age</strong>
                        <span>{{ $age !== null ? $age . ' years old' : '—' }}</span>
                    </div>
                </div>

                <hr style="border: none; border-top: 1px solid var(--border); margin-bottom: 30px;">

                {{-- === SECTION 2: ACCOUNT CREDENTIALS === --}}
                <span class="section-title">2. Account Credentials</span>
                <div class="detail-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 30px;">
                    <div class="detail-item">
                        <strong>Username</strong>
                        <span>{{ '@' . ($application->user->username ?? '—') }}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Email Address</strong>
                        <span>{{ $application->user->email ?? '—' }}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Account Role</strong>
                        <span style="text-transform: capitalize;">{{ $application->user->role ?? '—' }}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Doctor Status</strong>
                        <span class="badge {{ $application->status }}" style="font-size: 0.7rem;">{{ ucfirst($application->status) }}</span>
                    </div>
                </div>

                <hr style="border: none; border-top: 1px solid var(--border); margin-bottom: 30px;">

                {{-- === SECTION 3: PROFESSIONAL INFORMATION === --}}
                <span class="section-title">3. Professional Information</span>
                <div class="detail-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 30px;">
                    <div class="detail-item">
                        <strong>Professional Title</strong>
                        <span>{{ $application->professional_titles ?? 'Not specified' }}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Submitted On</strong>
                        <span>{{ $application->submitted_at->format('M d, Y') }}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Time Submitted</strong>
                        <span>{{ $application->submitted_at->format('h:i A') }}</span>
                    </div>
                </div>

                <hr style="border: none; border-top: 1px solid var(--border); margin-bottom: 30px;">

                {{-- === SECTION 4: BIOMETRIC VERIFICATION === --}}
                <span class="section-title">4. Biometric Verification</span>
                <div class="detail-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 30px;">
                    <div class="detail-item">
                        <strong>Biometric Consent</strong>
                        @if($application->biometric_consent)
                            <span style="color: #059669; font-weight: 600;">✓ Agreed</span>
                        @else
                            <span style="color: #dc2626;">✗ Not Given</span>
                        @endif
                    </div>
                    <div class="detail-item">
                        <strong>Liveness Verified</strong>
                        @if($application->liveness_verified)
                            <span style="color: #059669; font-weight: 600;">✓ Verified</span>
                        @else
                            <span style="color: #dc2626;">✗ Not Verified</span>
                        @endif
                    </div>
                    <div class="detail-item">
                        <strong>Face Match Score</strong>
                        @php $score = floatval($application->face_match_score); @endphp
                        <span style="font-weight: 600; color: {{ $score >= 90 ? '#059669' : ($score >= 70 ? '#d97706' : '#dc2626') }};">
                            {{ $score > 0 ? number_format($score, 2) . '%' : '—' }}
                        </span>
                    </div>
                    <div class="detail-item">
                        <strong>Verified At</strong>
                        <span>{{ $application->biometric_verified_at ? \Carbon\Carbon::parse($application->biometric_verified_at)->format('M d, Y h:i A') : '—' }}</span>
                    </div>
                </div>

                <hr style="border: none; border-top: 1px solid var(--border); margin-bottom: 30px;">

                {{-- === SECTION 5: SUBMITTED DOCUMENTS === --}}
                <span class="section-title">5. Requirements Verification — Submitted Documents</span>

                @php
                    // Key submitted docs by requirement_id for easy lookup
                    $submittedDocs = $application->documents->keyBy('doctor_requirement_id');
                    // Docs without a linked requirement (orphaned)
                    $orphanedDocs = $application->documents->filter(fn($d) => !$d->requirement);
                @endphp

                @if(isset($requirements) && $requirements->isNotEmpty())
                    <div class="document-list">
                        @foreach($requirements as $req)
                            @php $doc = $submittedDocs->get($req->id); @endphp
                            <div class="document-item" style="flex-wrap: wrap; gap: 10px; {{ !$doc && $req->is_required ? 'border-left: 4px solid #ef4444;' : (!$doc ? 'border-left: 4px solid #d97706;' : 'border-left: 4px solid #059669;') }}">
                                <div style="flex: 1; min-width: 200px;">
                                    <div class="doc-name">
                                        {{ $req->name }}
                                        @if($req->is_required)
                                            <span style="background: rgba(239,68,68,0.1); color:#ef4444; padding: 2px 7px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; margin-left: 6px;">Required</span>
                                        @else
                                            <span style="background: var(--input-bg); color: var(--muted); padding: 2px 7px; border-radius: 4px; font-size: 0.7rem; margin-left: 6px;">Optional</span>
                                        @endif
                                    </div>
                                    <div class="doc-desc">{{ $req->description ?? 'No description provided.' }}</div>

                                    @if(!$doc)
                                        @if($req->is_required)
                                            <div style="margin-top: 8px; padding: 8px 12px; background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.3); border-radius: 6px; font-size: 0.82rem; color: #dc2626;">
                                                ⚠ <strong>Not Submitted</strong> — This required document was not uploaded. The application may be incomplete.
                                            </div>
                                        @else
                                            <div style="margin-top: 8px; padding: 8px 12px; background: rgba(217,119,6,0.08); border: 1px solid rgba(217,119,6,0.25); border-radius: 6px; font-size: 0.82rem; color: #b45309;">
                                                ℹ <strong>Not Submitted</strong> — This optional document was not provided by the applicant.
                                            </div>
                                        @endif
                                    @else
                                        @php
                                            $ext = $doc->file_path ? strtoupper(pathinfo($doc->file_path, PATHINFO_EXTENSION)) : null;
                                            $isVideo = in_array(strtolower($ext ?? ''), ['mp4', 'webm', 'mov', 'avi']);
                                        @endphp
                                        <div style="margin-top: 8px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                            @if($ext)
                                                <span style="background: rgba(59,130,246,0.1); color:#3b82f6; padding: 2px 8px; border-radius: 4px; font-size:0.75rem; font-weight:700;">
                                                    {{ $isVideo ? '🎥' : '📄' }} {{ $ext }}
                                                </span>
                                            @endif
                                            <span style="font-size: 0.8rem; color: var(--muted);">{{ basename($doc->file_path ?? '—') }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div style="display: flex; align-items: center; gap: 10px; flex-shrink: 0;">
                                    @if($doc)
                                        <span style="font-size: 0.8rem; color: {{ $doc->status === 'accepted' ? '#059669' : ($doc->status === 'rejected' ? '#dc2626' : '#d97706') }}; font-weight: 600; text-transform: capitalize; min-width: 65px; text-align: right;">
                                            {{ ucfirst($doc->status ?? 'submitted') }}
                                        </span>
                                        @if($doc->file_path)
                                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn-outline">
                                                <i data-lucide="external-link" style="width: 16px; height: 16px;"></i>
                                                View
                                            </a>
                                        @endif
                                    @else
                                        <span style="font-size: 0.8rem; color: var(--muted); font-style: italic;">No file</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p style="color: var(--muted); padding: 20px; text-align: center; background: var(--bg); border-radius: 8px;">No requirement definitions found.</p>
                @endif

                {{-- === REVIEW DECISION === --}}
                <div class="review-actions">
                    @if($application->status === 'pending')
                        <span class="section-title">Review Decision</span>
                        <div class="review-form-grid">
                            <div class="review-card" style="border-top: 4px solid #059669;">
                                <h3 style="font-size: 1.1rem; margin-bottom: 15px; color: #059669;">Approve Application</h3>
                                <form action="{{ route('admin.applications.approve', $application->id) }}" method="POST">
                                    @csrf
                                    <textarea name="admin_notes" placeholder="Add approval notes for the doctor (optional)..."></textarea>
                                    <button type="submit" class="btn-solid btn-approve" style="width: 100%;">
                                        Approve &amp; Verify Doctor
                                    </button>
                                </form>
                            </div>
                            <div class="review-card" style="border-top: 4px solid #dc2626;">
                                <h3 style="font-size: 1.1rem; margin-bottom: 15px; color: #dc2626;">Reject Application</h3>
                                <form action="{{ route('admin.applications.reject', $application->id) }}" method="POST">
                                    @csrf
                                    <textarea name="admin_notes" placeholder="Explain the reason for rejection (required)..." required></textarea>
                                    <button type="submit" class="btn-solid btn-reject" style="width: 100%;">
                                        Reject Application
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <span class="section-title">Review History</span>
                        <div class="notes-box" style="border-left-color: {{ $application->status === 'approved' ? '#059669' : '#dc2626' }};">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                                <strong style="font-size: 1rem; color: var(--text);">Administrator Feedback</strong>
                                <span style="font-size: 0.8rem; color: var(--muted);">{{ $application->reviewed_at->format('M d, Y h:i A') }}</span>
                            </div>
                            <p style="white-space: pre-wrap; margin: 0; color: var(--text); line-height: 1.6;">{{ $application->admin_notes ?? 'No additional notes provided by the administrator.' }}</p>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </section>
  </div>
</main>
@endsection

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
                filterForm.submit();
            }, 500);
        });

        if (searchInput.value && document.activeElement !== searchInput) {
            // Only refocus if the search input had a value (implies we just returned or changed it)
            // But be careful not to trigger it unnecessarily
        }
    }
});
</script>
@endpush
