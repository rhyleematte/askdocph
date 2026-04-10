@extends('layouts.dashboard')

@section('title', 'Create Appointment | AskDocPH')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/appointments_ios.css') }}">
    <!-- Trendy Pickers (Flatpickr) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/light.css">
    <!-- Custom styling to make the creation page feel full-screen and primary -->
    <style>
        .creation-page-wrapper {
            padding-top: 80px;
            /* Account for fixed header */
            background: var(--bg);
            min-height: 100vh;
        }

        /* Override modal-specific constraints if any */
        .apt-view-container {
            height: auto !important;
            min-height: calc(100vh - 80px);
        }

        /* Edit Visual Overlay */
        .visual-placeholder-ios {
            cursor: pointer;
            transition: transform 0.2s;
        }

        .visual-placeholder-ios:hover {
            transform: scale(1.01);
        }

        .visual-edit-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.9rem;
            opacity: 0;
            transition: opacity 0.2s;
            backdrop-filter: blur(4px);
        }

        .visual-placeholder-ios:hover .visual-edit-overlay {
            opacity: 1;
        }
    </style>
@endpush

@section('content')
    <div class="creation-page-wrapper">
        <!-- Premium Highlight Orbs -->
        <div class="highlight-orb orb-red"></div>
        <div class="highlight-orb orb-purple"></div>

        <div id="aptViewCreate" class="apt-view-container">
            <div class="create-body">
                <!-- Left: Main Form -->
                <div class="create-main">
                    <div class="discard-draft" onclick="window.history.back()">
                        <i data-lucide="chevron-left"></i> DISCARD DRAFT
                    </div>

                    <div class="title-section">
                        <textarea id="aptSubject" class="massive-title-input" placeholder="Appointment Title" required
                            autofocus rows="1"></textarea>
                        <div class="title-underline"></div>
                    </div>

                    <div class="hero-inputs-grid">
                        <div class="hero-pill-wrap">
                            <label class="section-label">DATE</label>
                            <div class="hero-pill" onclick="toggleSuite('dateSuite')">
                                <i data-lucide="calendar" class="pill-icon-red"></i>
                                <div class="hero-pill-content">
                                    <div id="displayAptDate" class="hero-pill-value">October 24, 2024</div>
                                </div>
                            </div>
                            <div id="dateSuite" class="selection-suite-ios">
                                <input type="text" id="dateInput" style="display:none;" required>
                            </div>
                        </div>

                        <div class="hero-pill-wrap">
                            <label class="section-label">START TIME</label>
                            <div class="hero-pill" onclick="toggleSuite('startSuite')">
                                <i data-lucide="clock" class="pill-icon-red"></i>
                                <div class="hero-pill-content">
                                    <span id="displayStartTime" class="hero-pill-value">10:00 AM</span>
                                </div>
                            </div>
                            <div id="startSuite" class="selection-suite-ios">
                                <input type="text" id="startTimeInput" style="display:none;">
                            </div>
                        </div>

                        <div class="hero-pill-wrap">
                            <label class="section-label">END TIME</label>
                            <div class="hero-pill" onclick="toggleSuite('endSuite')">
                                <i data-lucide="clock" class="pill-icon-red"></i>
                                <div class="hero-pill-content">
                                    <span id="displayEndTime" class="hero-pill-value">11:00 AM</span>
                                </div>
                            </div>
                            <div id="endSuite" class="selection-suite-ios">
                                <input type="text" id="endTimeInput" style="display:none;">
                            </div>
                        </div>
                    </div>

                    <!-- Final Submission Data (Hidden) -->
                    <input type="hidden" id="aptStart" required>
                    <input type="hidden" id="aptEnd" required>

                    <div class="hero-pill-wrap location-pill-wrap" style="margin-top: 1.5rem;">
                        <label class="section-label">LOCATION</label>
                        <div class="hero-pill">
                            <i data-lucide="map-pin" class="pill-icon-red"></i>
                            <input type="text" id="aptLocation" class="hero-pill-input"
                                placeholder="Add a studio or virtual link">
                        </div>
                    </div>

                    <div class="visual-placeholder-ios" onclick="document.getElementById('aptCoverInput').click()">
                        <img id="aptCoverPreview" src="{{ asset('assets/img/appointment_default.jpg') }}"
                            alt="Default Placeholder" class="ios-visual-img">
                        <div class="visual-edit-overlay">
                            <i data-lucide="camera" style="margin-right: 8px;"></i> EDIT STUDIO VISUAL
                        </div>
                    </div>
                    <input type="file" id="aptCoverInput" style="display:none;" accept="image/*">

                    <label class="section-label">EDITORIAL NOTES</label>
                    <textarea id="aptDescription" class="editorial-textarea-ios"
                        placeholder="Describe the creative intent or agenda..."></textarea>
                </div>

                <!-- Right: Sidebar -->
                <div class="create-sidebar">
                    <div class="action-card-ios">
                        <button type="button" id="btnSubmitApt" class="btn-create-apt-red">Create Appointment</button>
                        <div class="workspace-label-mini">WORKSPACE: EDITORIAL STUDIO</div>
                    </div>

                    <div class="participants-section-ios">
                        <div class="sidebar-header-row">
                            <label class="section-label">PARTICIPANTS</label>
                            <div class="ios-btn-link-red" onclick="document.getElementById('aptUserSearch').focus()">+ ADD
                                NEW</div>
                        </div>

                        <div id="aptSelectedUsers" class="participants-stack">
                            <!-- Selected participants will appear here dynamically -->
                        </div>

                        <div style="position:relative; margin-top: 1rem;">
                            <input type="text" id="aptUserSearch" class="ios-search-minimal" placeholder="Search users...">
                            <div id="aptSearchResults" class="search-dropdown-ios"></div>
                        </div>

                    </div>


                    <div id="aptActionError" class="ios-error-box" style="margin-bottom: 1.5rem;"></div>

                    <div id="aptConflictAlert" class="ios-conflict-alert-v2"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Flag to tell appointments.js that we are on a standalone page
        window.IS_CREATION_PAGE = true;

        document.addEventListener('DOMContentLoaded', function () {
            // Smart Title Logic: Auto-height and 50-word limit
            const aptSubject = document.getElementById('aptSubject');
            if (aptSubject) {
                const adjustHeight = () => {
                    aptSubject.style.height = 'auto';
                    aptSubject.style.height = aptSubject.scrollHeight + 'px';
                };

                aptSubject.addEventListener('input', function () {
                    if (this.value.length > 50) {
                        this.value = this.value.substring(0, 50);
                    }
                    adjustHeight();
                });

                adjustHeight();
            }

            // Centralized Sync Logic for Frontend -> Backend Data Integrity
            const hiddenStart = document.getElementById('aptStart');
            const hiddenEnd = document.getElementById('aptEnd');
            const dateDisp = document.getElementById('displayAptDate');
            const startDisp = document.getElementById('displayStartTime');
            const endDisp = document.getElementById('displayEndTime');

            function syncAptDateTime() {
                const dateVal = datePicker.selectedDates[0];
                const startT = startTimePicker.selectedDates[0];
                const endT = endTimePicker.selectedDates[0];

                if (dateVal && startT && endT) {
                    // Combine Date + Start Time
                    const start = new Date(dateVal);
                    start.setHours(startT.getHours(), startT.getMinutes(), 0);

                    // Combine Date + End Time
                    const end = new Date(dateVal);
                    end.setHours(endT.getHours(), endT.getMinutes(), 0);

                    // Guardrail: End must be after Start
                    if (end <= start) {
                        end.setTime(start.getTime() + 60 * 60000); // 1 hour default nudge
                        endTimePicker.setDate(end, false);
                    }

                    // Update Hidden ISO Values for Backend & Conflict Check
                    const toIso = (d) => d.getFullYear() + '-' +
                        String(d.getMonth() + 1).padStart(2, '0') + '-' +
                        String(d.getDate()).padStart(2, '0') + 'T' +
                        String(d.getHours()).padStart(2, '0') + ':' +
                        String(d.getMinutes()).padStart(2, '0');

                    const startISO = toIso(start);
                    hiddenStart.value = startISO;
                    hiddenEnd.value = toIso(end);

                    // Guardrail: Cannot schedule in the past (allow current minute)
                    const errDisp = document.getElementById('aptActionError');
                    const now = new Date();
                    now.setSeconds(0, 0); // Ignore seconds for a smoother "immediate" check

                    if (start < now) {
                        if (errDisp) {
                            errDisp.innerText = "Appointment cannot be in the past.";
                            errDisp.style.display = 'block';
                        }
                    } else {
                        if (errDisp && errDisp.innerText === "Appointment cannot be in the past.") {
                            errDisp.style.display = 'none';
                        }
                    }

                    // Update UI Pills
                    dateDisp.innerText = start.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                    startDisp.innerText = start.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
                    endDisp.innerText = end.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });

                    // Trigger conflict check (in appointments.js)
                    hiddenStart.dispatchEvent(new Event('change'));
                }
            }

            // Inline Selection Logic
            window.toggleSuite = function (suiteId) {
                const suites = ['dateSuite', 'startSuite', 'endSuite'];
                suites.forEach(s => {
                    const el = document.getElementById(s);
                    if (s === suiteId) {
                        el.classList.toggle('expanded');
                    } else {
                        el.classList.remove('expanded');
                    }
                });
            };

            // Initialize Trendy Pickers (Flatpickr)
            const datePicker = flatpickr("#dateInput", {
                inline: true,
                enableTime: false,
                dateFormat: "Y-m-d",
                defaultDate: "today",
                minDate: "today",
                onChange: function (selectedDates, dateStr, instance) {
                    syncAptDateTime();
                    window.toggleSuite(null);
                }
            });

            const startTimePicker = flatpickr("#startTimeInput", {
                inline: true,
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                defaultDate: "10:00",
                onChange: function(selectedDates, dateStr, instance) {
                    syncAptDateTime();
                    window.toggleSuite(null);
                }
            });

            const endTimePicker = flatpickr("#endTimeInput", {
                inline: true,
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                defaultDate: "11:00",
                onChange: function(selectedDates, dateStr, instance) {
                    syncAptDateTime();
                    window.toggleSuite(null);
                }
            });

            // Initial Sync
            setTimeout(syncAptDateTime, 100);

            // Real-time Visual Preview Logic
            const coverInput = document.getElementById('aptCoverInput');
            const coverPreview = document.getElementById('aptCoverPreview');

            if (coverInput && coverPreview) {
                coverInput.addEventListener('change', function () {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            coverPreview.src = e.target.result;
                            coverPreview.style.opacity = '1';
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>
@endpush