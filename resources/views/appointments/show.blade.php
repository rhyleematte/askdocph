@extends('layouts.dashboard')

@section('title', 'Appointment Details | AskDocPH')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/appointments_ios.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .creation-page-wrapper {
            padding-top: 80px;
            padding-bottom: 50px;
            {{ $isDeleted ? 'filter: grayscale(1); pointer-events: none;' : '' }}
            background: var(--bg);
            min-height: 100vh;
        }

        .appointment-deleted-alert {
            background: #fff5f5;
            border: 1px solid #feb2b2;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            color: #c53030;
            {{ $isDeleted ? 'filter: none !important; pointer-events: auto !important;' : '' }}
        }

        .alert-icon i {
            width: 32px;
            height: 32px;
        }

        .alert-content h3 {
            margin: 0 0 4px 0;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .alert-content p {
            margin: 0;
            font-size: 0.95rem;
            opacity: 0.8;
        }

        .deleted-opaque {
            opacity: 0.5;
        }

        .apt-view-container {
            height: auto !important;
            min-height: calc(100vh - 80px);
        }

        .read-only-pill {
            cursor: default !important;
        }

        .status-badge-ios {
            font-size: 0.7rem;
            font-weight: 800;
            padding: 4px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: 10px;
        }

        .status-pending {
            background: rgba(246, 194, 62, 0.1);
            color: #f6c23e;
        }

        .status-accepted {
            background: rgba(28, 200, 138, 0.1);
            color: #1cc88a;
        }

        .status-declined {
            background: rgba(231, 74, 59, 0.1);
            color: #e74a3b;
        }

        .btn-accept-ios {
            background: #1cc88a !important;
            color: white;
            border: none;
            width: 100%;
            padding: 1.2rem;
            border-radius: 16px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(28, 200, 138, 0.3) !important;
            margin-bottom: 12px;
            transition: transform 0.2s;
        }

        .btn-decline-ios {
            background: #f8f9fa !important;
            color: #495057;
            border: 1px solid #dee2e6;
            width: 100%;
            padding: 1.2rem;
            border-radius: 16px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-decline-ios:hover {
            background: #e9ecef !important;
        }

        .btn-accept-ios:active,
        .btn-decline-ios:active {
            transform: scale(0.97);
        }
    </style>
@endpush

@section('content')
    <div class="creation-page-wrapper">
        <div id="aptViewShow" class="apt-view-container">
            <div class="create-body">
                <!-- Main Content -->
                <div class="create-main">
                    <div class="discard-draft" onclick="window.history.back()">
                        <i data-lucide="chevron-left"></i> BACK TO DASHBOARD
                    </div>

                    @if($isDeleted)
                        <div class="appointment-deleted-alert">
                            <div class="alert-icon">
                                <i data-lucide="alert-triangle"></i>
                            </div>
                            <div class="alert-content">
                                <h3>Appointment Canceled</h3>
                                <p>This appointment has been removed from the schedule by the organizer.</p>
                            </div>
                        </div>
                    @endif

                    <div class="title-section">
                        <h1 id="aptShowSubject" class="massive-title-input" style="color: var(--text); border: none; min-height: auto;">{{ $appointment->subject }}</h1>
                        <div class="title-underline"></div>
                    </div>

                    <div class="hero-inputs-grid">
                        <div class="hero-pill-wrap">
                            <label class="section-label">DATE</label>
                            <div class="hero-pill read-only-pill">
                                <i data-lucide="calendar" class="pill-icon-red"></i>
                                <div class="hero-pill-content">
                                    <div class="hero-pill-value">{{ $appointment->start_at->format('F j, Y') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="hero-pill-wrap">
                            <label class="section-label">TIME</label>
                            <div class="hero-pill read-only-pill">
                                <i data-lucide="clock" class="pill-icon-red"></i>
                                <div class="hero-pill-content">
                                    <span class="hero-pill-value">
                                        {{ $appointment->start_at->format('h:i A') }} —
                                        {{ $appointment->end_at->format('h:i A') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="hero-pill-wrap" style="margin-top: 1.5rem;">
                        <label class="section-label">LOCATION</label>
                        <div class="hero-pill read-only-pill">
                            <i data-lucide="map-pin" class="pill-icon-red"></i>
                            <div class="hero-pill-value">{{ $appointment->location ?? 'No location specified' }}</div>
                        </div>
                    </div>

                    <div class="visual-placeholder-ios"
                        style="height: 320px; display: flex; align-items: center; justify-content: center;">
                        <img src="{{ $appointment->cover_image ? asset($appointment->cover_image) : asset('assets/img/appointment_default.jpg') }}"
                            alt="Studio Visual" class="ios-visual-img" style="opacity: 1;">
                    </div>
                    <div class="editorial-notes-wrap {{ $isDeleted ? 'deleted-opaque' : '' }}">
                        <div class="section-label">Editorial Notes</div>
                        <div class="editorial-textarea-ios" style="min-height: 150px;">
                            {!! nl2br(e($appointment->description)) !!}
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="create-sidebar">
                    <div id="aptActionError" class="ios-btn-link-red"
                        style="display: none; margin-bottom: 1rem;">
                        <!-- Error messages appear here -->
                    </div>

                    <div class="action-card-ios">
                        @if($isCreator)
                            <button type="button" onclick="openEditModal()"
                                class="btn-create-apt-red">Update Appointment</button>
                            <button type="button" onclick="deleteApt()" class="btn-decline-ios"
                                style="margin-top: 1rem; width: 100%;">Delete Appointment</button>
                            <div class="workspace-label-mini">YOU ARE THE ORGANIZER</div>
                        @elseif($invitation)
                            @if($invitation->status === 'pending')
                                <button type="button" onclick="respondToInvite('accepted')" class="btn-accept-ios">Accept
                                    Invitation</button>
                                <button type="button" onclick="respondToInvite('declined')" class="btn-decline-ios">Decline</button>
                            @else
                                <div class="status-badge-ios status-{{ $invitation->status }}"
                                    style="display: block; width: 100%; text-align: center; padding: 1.2rem; font-size: 1rem;">
                                    Invitation {{ ucfirst($invitation->status) }}
                                </div>
                                @if($invitation->status === 'accepted')
                                    <button type="button" onclick="respondToInvite('declined')" class="ios-btn-link-red"
                                        style="margin-top: 1rem; display: block; width: 100%; text-align: center;">Cancel
                                        Participation</button>
                                    <div class="workspace-label-mini" style="margin-top: 1rem;">SCHEDULED ON YOUR CALENDAR</div>
                                @else
                                    <div class="workspace-label-mini" style="margin-top: 1rem;">NOT ON YOUR CALENDAR</div>
                                @endif
                            @endif
                        @endif
                    </div>

                    <div class="participants-section-ios">
                        <label class="section-label">PARTICIPANTS</label>
                        <div class="participants-stack">
                            <!-- Show Creator First -->
                            <div class="participant-card-ios">
                                <div class="p-card-avatar">
                                    <img src="{{ $appointment->creator->avatar_url }}" alt="Avatar">
                                </div>
                                <div class="p-card-info">
                                    <div class="p-card-name">{{ $appointment->creator->full_name }}</div>
                                    <div class="p-card-title">ORGANIZER</div>
                                </div>
                            </div>

                            <!-- Show Other Invitees -->
                            @foreach($appointment->invitations as $invite)
                                @if($invite->user_id !== $appointment->creator_id)
                                    <div class="participant-card-ios">
                                        <div class="p-card-avatar">
                                            <img src="{{ $invite->user->avatar_url }}" alt="Avatar">
                                        </div>
                                        <div class="p-card-info">
                                            <div class="p-card-name">
                                                {{ $invite->user->full_name }}
                                                <span
                                                    class="status-badge-ios status-{{ $invite->status }}">{{ $invite->status }}</span>
                                            </div>
                                            <div class="p-card-title">GUEST</div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Edit Appointment Modal ──────────────────────────────── --}}
    <div id="editAptModal" style="display:none; position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center;">
        <div style="position:absolute; inset:0; background:rgba(0,0,0,0.55); backdrop-filter:blur(4px);" onclick="closeEditModal()"></div>
        <div id="editAptBox" style="position:relative; background:var(--panel); border:1px solid var(--border); border-radius:24px; padding:2.5rem; width:95%; max-width:560px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.3);">

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
                <h2 style="font-size:1.4rem; font-weight:800; color:var(--text); margin:0;">Edit Appointment</h2>
                <button onclick="closeEditModal()" style="background:none; border:none; cursor:pointer; color:var(--muted); padding:4px;">
                    <i data-lucide="x" style="width:22px; height:22px;"></i>
                </button>
            </div>

            <div id="editAptError" class="ios-error-box" style="margin-bottom: 1.2rem;"></div>

            {{-- Title --}}
            <div style="margin-bottom:1.2rem;">
                <label style="display:block; font-size:0.72rem; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; color:var(--muted); margin-bottom:6px;">Title</label>
                <input id="editSubject" type="text" value="{{ $appointment->subject }}"
                    style="width:100%; background:var(--input-bg); border:1px solid var(--border); border-radius:12px; padding:12px 16px; font-size:1rem; color:var(--text); outline:none; transition:border-color 0.2s;"
                    onfocus="this.style.borderColor='#dc3545'" onblur="this.style.borderColor='var(--border)'">
            </div>

            {{-- Location --}}
            <div style="margin-bottom:1.2rem;">
                <label style="display:block; font-size:0.72rem; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; color:var(--muted); margin-bottom:6px;">Location</label>
                <input id="editLocation" type="text" value="{{ $appointment->location }}"
                    placeholder="Add a location"
                    style="width:100%; background:var(--input-bg); border:1px solid var(--border); border-radius:12px; padding:12px 16px; font-size:1rem; color:var(--text); outline:none; transition:border-color 0.2s;"
                    onfocus="this.style.borderColor='#dc3545'" onblur="this.style.borderColor='var(--border)'">
            </div>

            {{-- Date & Time --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:1.2rem;">
                <div>
                    <label style="display:block; font-size:0.72rem; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; color:var(--muted); margin-bottom:6px;">Start</label>
                    <input id="editStart" type="datetime-local" value="{{ $appointment->start_at->format('Y-m-d\TH:i') }}"
                        style="width:100%; background:var(--input-bg); border:1px solid var(--border); border-radius:12px; padding:10px 12px; font-size:0.9rem; color:var(--text); outline:none; transition:border-color 0.2s;"
                        onfocus="this.style.borderColor='#dc3545'" onblur="this.style.borderColor='var(--border)'">
                </div>
                <div>
                    <label style="display:block; font-size:0.72rem; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; color:var(--muted); margin-bottom:6px;">End</label>
                    <input id="editEnd" type="datetime-local" value="{{ $appointment->end_at->format('Y-m-d\TH:i') }}"
                        style="width:100%; background:var(--input-bg); border:1px solid var(--border); border-radius:12px; padding:10px 12px; font-size:0.9rem; color:var(--text); outline:none; transition:border-color 0.2s;"
                        onfocus="this.style.borderColor='#dc3545'" onblur="this.style.borderColor='var(--border)'">
                </div>
            </div>

            {{-- Description --}}
            <div style="margin-bottom:1.8rem;">
                <label style="display:block; font-size:0.72rem; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; color:var(--muted); margin-bottom:6px;">Notes / Description</label>
                <textarea id="editDescription" rows="4"
                    style="width:100%; background:var(--input-bg); border:1px solid var(--border); border-radius:12px; padding:12px 16px; font-size:0.95rem; color:var(--text); outline:none; resize:none; line-height:1.6; transition:border-color 0.2s;"
                    onfocus="this.style.borderColor='#dc3545'" onblur="this.style.borderColor='var(--border)'">{{ $appointment->description }}</textarea>
            </div>

            {{-- Cover Image --}}
            <div style="margin-bottom:1.8rem;">
                <label style="display:block; font-size:0.72rem; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; color:var(--muted); margin-bottom:6px;">Cover Image <span style="font-weight:400; text-transform:none;">(optional)</span></label>
                <input id="editCoverImage" type="file" accept="image/*"
                    style="width:100%; background:var(--input-bg); border:1px solid var(--border); border-radius:12px; padding:10px 14px; font-size:0.85rem; color:var(--muted); cursor:pointer;">
            </div>

            {{-- Actions --}}
            <div style="display:flex; gap:12px;">
                <button id="editSaveBtn" onclick="submitEditApt()" style="flex:1; background:linear-gradient(135deg,#dc3545,#b02a37); color:white; border:none; padding:1rem; border-radius:14px; font-size:1rem; font-weight:700; cursor:pointer; transition:opacity 0.2s; box-shadow:0 6px 20px rgba(220,53,69,0.3);">Save Changes</button>
                <button onclick="closeEditModal()" style="padding:1rem 1.5rem; background:var(--hover); color:var(--text); border:1px solid var(--border); border-radius:14px; font-size:1rem; font-weight:600; cursor:pointer;">Cancel</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const APT_UPDATE_URL = "{{ route('appointments.update', $appointment->id) }}";
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function openEditModal() {
            const modal = document.getElementById('editAptModal');
            modal.style.display = 'flex';
            if (window.lucide) lucide.createIcons();
        }

        function closeEditModal() {
            document.getElementById('editAptModal').style.display = 'none';
            document.getElementById('editAptError').style.display = 'none';
        }

        async function submitEditApt() {
            const btn = document.getElementById('editSaveBtn');
            const errBox = document.getElementById('editAptError');
            errBox.style.display = 'none';

            const subject = document.getElementById('editSubject').value.trim();
            if (!subject) {
                errBox.innerText = 'Title is required.';
                errBox.style.display = 'block';
                return;
            }

            const formData = new FormData();
            formData.append('subject',     subject);
            formData.append('location',    document.getElementById('editLocation').value.trim());
            formData.append('description', document.getElementById('editDescription').value.trim());
            formData.append('start_at',    document.getElementById('editStart').value);
            formData.append('end_at',      document.getElementById('editEnd').value);

            const coverFile = document.getElementById('editCoverImage').files[0];
            if (coverFile) formData.append('cover_image', coverFile);

            btn.disabled = true;
            btn.innerText = 'Saving...';

            try {
                const res = await fetch(APT_UPDATE_URL, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    body: formData,
                });

                const data = await res.json();

                if (data.ok) {
                    closeEditModal();
                    location.reload();
                } else {
                    const msg = data.errors
                        ? Object.values(data.errors)[0][0]
                        : (data.message || 'Failed to update.');
                    errBox.innerText = msg;
                    errBox.style.display = 'block';
                }
            } catch (e) {
                errBox.innerText = 'Network error. Please try again.';
                errBox.style.display = 'block';
            } finally {
                btn.disabled = false;
                btn.innerText = 'Save Changes';
            }
        }

        // Close modal on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeEditModal();
        });
        async function respondToInvite(status) {
            if (!confirm(`Are you sure you want to ${status} this invitation?`)) return;

            const errDisp = document.getElementById('aptActionError');
            if (errDisp) errDisp.style.display = 'none';

            try {
                const response = await fetch("{{ route('appointments.respond', $invitation->id ?? 0) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status })
                });

                const result = await response.json();
                if (result.ok) {
                    location.reload();
                } else {
                    if (errDisp) {
                        errDisp.innerText = result.message || 'Failed to respond to invitation.';
                        errDisp.style.display = 'block';
                    } else {
                        alert(result.message || 'Failed to respond to invitation.');
                    }
                }
            } catch (error) {
                console.error('Error responding to invitation:', error);
                if (errDisp) {
                    errDisp.innerText = 'An error occurred. Please try again.';
                    errDisp.style.display = 'block';
                } else {
                    alert('An error occurred. Please try again.');
                }
            }
        }

        async function deleteApt() {
            if (!confirm('Are you sure you want to DELETE this appointment? This action cannot be undone.')) return;

            const errDisp = document.getElementById('aptActionError');
            if (errDisp) errDisp.style.display = 'none';

            try {
                const response = await fetch("{{ route('appointments.destroy', $appointment->id) }}", {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();
                if (result.ok) {
                    location.reload();
                } else {
                    if (errDisp) {
                        errDisp.innerText = result.message || 'Failed to delete appointment.';
                        errDisp.style.display = 'block';
                    } else {
                        alert(result.message || 'Failed to delete appointment.');
                    }
                }
            } catch (error) {
                console.error('Error deleting appointment:', error);
                if (errDisp) {
                    errDisp.innerText = 'An error occurred. Please try again.';
                    errDisp.style.display = 'block';
                } else {
                    alert('An error occurred. Please try again.');
                }
            }
        }
    </script>
@endpush