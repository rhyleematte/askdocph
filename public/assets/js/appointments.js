document.addEventListener('DOMContentLoaded', function () {
    const calendarBtn = document.getElementById('headerScheduleBtn');
    const sidebarBtn = document.getElementById('sidebarScheduleBtn');
    const addAptBtn = document.getElementById('headerAddAptBtn');
    const modal = document.getElementById('appointmentsModal');
    const closeBtn = document.getElementById('appointmentsCloseBtn');
    const backdrop = document.getElementById('appointmentsBackdrop');

    // iOS Header Elements
    const iosMonthTitle = document.getElementById('iosCurrentMonth');
    const iosYearNav = document.getElementById('iosYearNav');
    const iosBackLink = document.getElementById('iosBackLink');
    const iosBackLabel = document.getElementById('iosBackLabel');
    const iosDefaultHeader = document.getElementById('iosDefaultHeader');
    const iosSearchHeader = document.getElementById('iosSearchHeader');
    const iosSearchToggle = document.getElementById('iosSearchToggle');
    const iosSearchCancel = document.getElementById('iosSearchCancel');
    const iosNavToday = document.getElementById('iosNavToday');

    let calendar;
    let selectedInvitedUsers = [];
    let currentEventId = null;
    let currentInvitationId = null;
    let lastRefetchTime = 0;

    const openModal = () => {
        modal.style.display = 'block';
        if (window.lucide) lucide.createIcons();
        if (!calendar) {
            initCalendar();
            lastRefetchTime = Date.now();
            // Show today's agenda by default
            setTimeout(() => renderDailyAgenda(new Date()), 500);
        } else {
            calendar.render();
            // Only refetch if data is older than 5 minutes
            if (Date.now() - lastRefetchTime > 300000) {
                calendar.refetchEvents();
                lastRefetchTime = Date.now();
            }
            if (window.lucide) lucide.createIcons();
        }
    };

    const closeModal = () => {
        modal.style.display = 'none';
        // Reset to calendar view when closing the whole modal
        switchAptView('calendar');
        document.querySelectorAll('.apt-sub-modal').forEach(sub => sub.style.display = 'none');
    };
    window.closeModal = closeModal;

    if (calendarBtn) calendarBtn.addEventListener('click', openModal);
    if (sidebarBtn) sidebarBtn.addEventListener('click', openModal);

    if (iosNavToday) {
        iosNavToday.addEventListener('click', () => {
            calendar.today();
            if (calendar.view.type !== 'dayGridMonth' && calendar.view.type !== 'timeGridDay') {
                calendar.changeView('dayGridMonth');
            }
        });
    }

    if (iosBackLink) {
        iosBackLink.addEventListener('click', () => {
            if (calendar.view.type === 'timeGridDay') {
                calendar.changeView('dayGridMonth');
            } else if (calendar.view.type === 'dayGridMonth') {
                calendar.changeView('multiMonthYear');
            }
        });
    }

    if (iosSearchToggle) {
        iosSearchToggle.addEventListener('click', () => {
            iosDefaultHeader.style.display = 'none';
            iosSearchHeader.style.display = 'flex';
            setTimeout(() => document.getElementById('iosSearchInput').focus(), 100);
        });
    }

    if (iosSearchCancel) {
        iosSearchCancel.addEventListener('click', () => {
            iosDefaultHeader.style.display = 'flex';
            iosSearchHeader.style.display = 'none';
            // Clear filter
            document.getElementById('iosSearchInput').value = '';
            calendar.refetchEvents();
        });
    }

    const iosSearchInput = document.getElementById('iosSearchInput');

    if (iosSearchInput) {
        iosSearchInput.addEventListener('input', (e) => {
            const q = e.target.value.toLowerCase();
            const events = calendar.getEvents();
            events.forEach(ev => {
                if (ev.title.toLowerCase().includes(q)) {
                    ev.setProp('display', 'auto');
                } else {
                    ev.setProp('display', 'none');
                }
            });
        });
    }

    if (addAptBtn) addAptBtn.addEventListener('click', () => {
        window.location.href = '/appointments/create';
    });

    // Handle initialization for standalone page
    if (window.IS_CREATION_PAGE) {
        initCreationPage();
    }

    function initCreationPage() {
        const now = new Date();
        const nowIso = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0') + 'T' + String(now.getHours()).padStart(2, '0') + ':00';

        const startInput = document.getElementById('aptStart');
        const endInput = document.getElementById('aptEnd');

        if (startInput && !startInput.value) startInput.value = nowIso;
        if (endInput && !endInput.value) endInput.value = nowIso;

        updateAptPillDisplays();
        if (window.lucide) lucide.createIcons();
    }

    function updateAptPillDisplays() {
        const startVal = document.getElementById('aptStart').value;
        const endVal = document.getElementById('aptEnd').value;
        const dateDisp = document.getElementById('displayAptDate');
        const timeDisp = document.getElementById('displayAptTime');

        if (startVal && dateDisp) {
            const date = new Date(startVal);
            dateDisp.innerText = date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
        }
        if (startVal && endVal && timeDisp) {
            const startStr = new Date(startVal).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
            const endStr = new Date(endVal).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
            timeDisp.innerText = `${startStr} — ${endStr}`;
        }
    }

    closeBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);

    if (iosYearNav) {
        iosYearNav.addEventListener('click', () => {
            calendar.changeView('multiMonthYear');
        });
    }

    function initCalendar() {
        const calendarEl = document.getElementById('appointmentsCalendar');
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: false,
            dayMaxEvents: 2,
            events: function (info, successCallback, failureCallback) {
                const start = encodeURIComponent(info.startStr);
                const end = encodeURIComponent(info.endStr);
                fetch(`/appointments/api/events?start=${start}&end=${end}`)
                    .then(res => {
                        if (!res.ok || !res.headers.get('content-type')?.includes('application/json')) {
                            res.text().then(text => {
                                console.error("Calendar Load Failed:", res.status, text.substring(0, 100));
                                if (res.status === 401) alert("Session Expired. Please refresh the page.");
                            });
                            throw new Error("HTTP " + res.status);
                        }
                        return res.json();
                    })
                    .then(data => successCallback(data))
                    .catch(error => {
                        console.error("FullCalendar JSON Error:", error);
                        failureCallback(error);
                    });
            },

            loading: function (isLoading) {
                if (!isLoading && calendar.view.type === 'dayGridMonth') {
                    // Refresh agenda after events load
                    renderDailyAgenda(calendar.getDate());
                }
            },

            navLinkDayClick: function (date, jsEvent) {
                const cal = this;
                if (cal.view.type === 'dayGridMonth') {
                    renderDailyAgenda(date);
                } else {
                    cal.changeView('timeGridDay', date);
                }
            },

            dateClick: function (info) {
                if (calendar.view.type === 'dayGridMonth') {
                    renderDailyAgenda(info.date);
                }
            },

            select: function (info) {
                if (calendar.view.type === 'dayGridMonth') {
                    renderDailyAgenda(info.start);
                } else if (calendar.view.type === 'multiMonthYear') {
                    calendar.changeView('dayGridMonth', info.start);
                } else {
                    document.getElementById('aptStart').value = info.startStr.slice(0, 16);
                    document.getElementById('aptEnd').value = info.endStr ? info.endStr.slice(0, 16) : info.startStr.slice(0, 16);
                    switchAptView('create');
                }
            },
            eventClick: function (info) {
                showAptDetails(info.event);
            },

            datesSet: function (info) {
                const cal = this;
                updateHeaderLabels(cal);
                updateWeekStrip(cal, info.start);
            },

            eventSourceFailure: function (error) {
                console.error("FullCalendar Fetch Error:", error);
                alert("Calendar Loading Error: The server returned an invalid response. Please check your connection or session.");
            }
        });
        calendar.render();
    }

    function updateHeaderLabels(cal) {
        if (!cal) return;
        const date = cal.getDate();
        const monthName = date.toLocaleDateString('default', { month: 'long' });
        const year = date.getFullYear();
        const view = cal.view.type;

        // Reset visibility
        iosBackLink.style.display = 'none';
        iosYearNav.style.display = 'none';
        iosCurrentMonth.style.display = 'block';

        if (view === 'multiMonthYear') {
            iosCurrentMonth.innerText = year;
            iosCurrentMonth.style.fontSize = '1.8rem';
            iosCurrentMonth.style.fontWeight = '800';
        } else if (view === 'dayGridMonth') {
            iosBackLink.style.display = 'flex';
            iosBackLabel.innerText = year;
            iosCurrentMonth.innerText = monthName;
            iosCurrentMonth.style.fontSize = '1.35rem';
            iosCurrentMonth.style.fontWeight = '700';
        } else if (view === 'timeGridDay') {
            iosBackLink.style.display = 'flex';
            iosBackLabel.innerText = monthName;
            // Center can show Month or specific date
            iosCurrentMonth.innerText = date.toLocaleDateString('default', { weekday: 'short', day: 'numeric' });
            iosCurrentMonth.style.fontSize = '1.2rem';
        }
    }

    function updateWeekStrip(cal, date) {
        if (!cal) return;
        let strip = document.getElementById('iosWeekStrip');
        if (!strip) {
            strip = document.createElement('div');
            strip.id = 'iosWeekStrip';
            strip.className = 'ios-week-strip';
            document.getElementById('appointmentsCalendar').prepend(strip);
        }

        if (cal.view.type !== 'timeGridDay') {
            strip.style.display = 'none';
            return;
        }

        strip.style.display = 'flex';
        strip.innerHTML = '';

        const startOfWeek = new Date(date);
        startOfWeek.setDate(date.getDate() - date.getDay());

        for (let i = 0; i < 7; i++) {
            const d = new Date(startOfWeek);
            d.setDate(startOfWeek.getDate() + i);

            const dayDiv = document.createElement('div');
            dayDiv.className = 'ios-week-day' + (d.toDateString() === date.toDateString() ? ' active' : '');
            dayDiv.innerHTML = `
                <span class="ios-week-label">${['S', 'M', 'T', 'W', 'T', 'F', 'S'][i]}</span>
                <span class="ios-week-number">${d.getDate()}</span>
            `;
            dayDiv.onclick = () => {
                cal.gotoDate(d);
                // Also update agenda if we somehow switch views
            };
            strip.appendChild(dayDiv);
        }
    }

    function renderDailyAgenda(date) {
        const agendaContainer = document.getElementById('aptDailyAgenda');
        if (!agendaContainer) return;

        const events = calendar.getEvents();
        const dateStr = date.toDateString();

        // Date info for Hero
        const dayNum = date.getDate().toString().padStart(2, '0');
        const monthName = date.toLocaleDateString('default', { month: 'long' });
        const weekday = date.toLocaleDateString('default', { weekday: 'long', year: 'numeric' });

        // Filter events for this day
        const dayEvents = events.filter(ev => {
            const start = ev.start;
            const end = ev.end || ev.start;
            const startStr = start.toDateString();
            const endStr = end.toDateString();
            return startStr === dateStr || endStr === dateStr || (start < date && end > date);
        });

        dayEvents.sort((a, b) => {
            if (a.extendedProps.type === 'holiday' && b.extendedProps.type !== 'holiday') return -1;
            if (a.extendedProps.type !== 'holiday' && b.extendedProps.type === 'holiday') return 1;
            return a.start - b.start;
        });

        let html = `
            <div class="digest-scroll-area">
                <div class="digest-header">
                    <div class="digest-badge-row">
                        <span class="digest-badge">Daily Digest</span>
                    </div>
                    <div class="digest-date-row">
                        <span class="digest-day-num">${dayNum}</span>
                        <div class="digest-day-info">
                            <span class="digest-month">${monthName}</span>
                            <span class="digest-weekday">${weekday}</span>
                        </div>
                    </div>
                </div>
                <div class="digest-content">
        `;

        if (dayEvents.length === 0) {
            html += `
                <div class="agenda-empty-state">
                    No events scheduled for this day
                </div>
            `;
        } else {
            dayEvents.forEach(ev => {
                const props = ev.extendedProps;
                const isHoliday = props.type === 'holiday';
                const startTime = ev.start ? ev.start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
                const endTime = ev.end ? ev.end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
                const timeRange = isHoliday ? 'Global Observance' : `${startTime} - ${endTime}`;

                html += `
                    <div class="digest-section-header">
                        <span class="digest-chip ${isHoliday ? 'chip-holiday' : ''}">${isHoliday ? 'Holiday' : 'Scheduled'}</span>
                        <span class="digest-observance">${isHoliday ? 'Global Observance' : ''}</span>
                    </div>
                    
                    ${!isHoliday ? `
                    <div class="digest-time-label">
                        <i data-lucide="clock"></i>
                        <span>${timeRange}</span>
                    </div>
                    ` : ''}

                    <div class="digest-card" ${!isHoliday ? `onclick="showAptDetailsById('${ev.id}')"` : 'style="cursor: default;"'}>
                        <div class="digest-card-title">${ev.title}</div>
                        ${isHoliday && props.description ? `<div class="digest-card-desc">${props.description}</div>` : ''}

                        <div class="digest-card-footer">
                            <div class="digest-avatars">
                                ${props.invited_users ? props.invited_users.slice(0, 3).map(u => `
                                    <img src="${u.avatar}" class="digest-avatar-item" title="${u.name}">
                                `).join('') : ''}
                                ${props.invited_users && props.invited_users.length > 3 ? `
                                    <div class="digest-avatar-more">+${props.invited_users.length - 3}</div>
                                ` : ''}
                            </div>

                            ${!isHoliday ? `
                            <div class="digest-view-link">
                                View Details <i data-lucide="arrow-right" style="width:14px;"></i>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            });
        }

        html += `
                </div>
            </div>
        `;

        agendaContainer.innerHTML = html;
        if (window.lucide) lucide.createIcons();
        agendaContainer.querySelector('.digest-scroll-area').scrollTop = 0;
    }

    window.showAptDetailsById = (id) => {
        const ev = calendar.getEventById(id);
        if (ev) showAptDetails(ev);
    };


    // Navigation helpers
    window.switchAptView = function (view) {
        if (view === 'create') {
            window.location.href = '/appointments/create';
            return;
        }
        const calView = document.getElementById('aptViewCalendar');
        const defaultHeader = document.getElementById('iosDefaultHeader');
        if (calView) calView.style.display = 'block';
        if (defaultHeader) defaultHeader.style.display = 'flex';
        if (calendar) {
            calendar.render();
            if (window.lucide) setTimeout(() => lucide.createIcons(), 50);
        }
    };

    // User Search Logic
    const searchInput = document.getElementById('aptUserSearch');
    const resultsEl = document.getElementById('aptSearchResults');
    if (searchInput) {
        searchInput.addEventListener('input', async (e) => {
            const q = e.target.value;
            if (q.length < 2) {
                resultsEl.style.display = 'none';
                return;
            }
            const res = await fetch(`/api/search/users?q=${q}`);
            if (!res.ok) {
                const text = await res.text();
                console.error("Search API Error:", res.status, text.substring(0, 200));
                alert("Search Error (" + res.status + "): " + text.substring(0, 100));
                return;
            }
            const data = await res.json();
            if (data.ok && data.users.length > 0) {
                resultsEl.innerHTML = data.users.map(u => `
                    <div class="search-res-item" onclick="selectAptUser(${u.id}, '${u.name.replace(/'/g, "\\'")}', '${u.avatar_url}')">
                        <img src="${u.avatar_url}" class="search-res-avatar" alt="${u.name}">
                        <div class="search-res-info">
                            <span class="search-res-name">${u.name}</span>
                            <span class="search-res-handle">@${u.username}</span>
                        </div>
                    </div>
                `).join('');
                resultsEl.style.display = 'block';
            } else {
                resultsEl.style.display = 'none';
            }
        });
    }

    window.selectAptUser = function (id, name, avatar) {
        if (selectedInvitedUsers.includes(id)) return;
        selectedInvitedUsers.push(id);

        // High-fidelity participant card
        const card = document.createElement('div');
        card.className = 'participant-item';
        card.innerHTML = `
            <img src="${avatar}" class="participant-avatar" alt="${name}">
            <div class="participant-info">
                <span class="participant-name">${name}</span>
                <span class="participant-role">Member</span>
            </div>
            <button type="button" class="participant-remove" onclick="removeAptUser(${id}, this)">&times;</button>
        `;

        document.getElementById('aptSelectedUsers').appendChild(card);
        resultsEl.style.display = 'none';
        searchInput.value = '';
        checkAptConflicts();
        if (window.lucide) lucide.createIcons();
    }

    window.removeAptUser = function (id, el) {
        selectedInvitedUsers = selectedInvitedUsers.filter(uid => uid !== id);
        el.closest('.participant-item').remove();
        checkAptConflicts();
    }

    const timeInputs = [document.getElementById('aptStart'), document.getElementById('aptEnd')];
    timeInputs.forEach(input => input && input.addEventListener('change', checkAptConflicts));

    async function checkAptConflicts() {
        const start = document.getElementById('aptStart').value;
        const end = document.getElementById('aptEnd').value;
        const warning = document.getElementById('aptConflictAlert');
        if (!start || !end) return;

        try {
            const userIdsQuery = selectedInvitedUsers.length > 0 ? '&' + selectedInvitedUsers.map(id => `user_ids[]=${id}`).join('&') : '';
            const res = await fetch(`/appointments/api/check-conflicts?start_at=${start}&end_at=${end}${userIdsQuery}`, {
                headers: { 'Accept': 'application/json' }
            });

            if (!res.ok || !res.headers.get('content-type')?.includes('application/json')) {
                const text = await res.text();
                console.warn("Conflict Check unavailable:", res.status, text.substring(0, 100));
                warning.innerText = "Conflict check currently unavailable. You can still try to save.";
                warning.style.color = "var(--ios-accent)";
                warning.style.display = 'block';
                return;
            }

            const data = await res.json();
            if (data.has_conflict) {
                const formatTime = (iso) => new Date(iso).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
                const formatDate = (iso) => new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });

                const msgs = data.conflicts.map(c => {
                    const dateStr = formatDate(c.start);
                    const startT = formatTime(c.start);
                    const endT = formatTime(c.end);
                    return `Conflict: ${c.name} has a scheduled session of '${c.subject}' on ${dateStr} from ${startT} to ${endT}.`;
                });

                warning.innerText = msgs.join('\n');
                warning.style.color = "#856404"; // Darker yellow/brown for wariness
                warning.style.display = 'block';
            } else {
                warning.style.display = 'none';
            }
        } catch (e) {
            console.error("Conflict fetch error:", e);
        }
    }

    const submitBtn = document.getElementById('btnSubmitApt');
    if (submitBtn) {
        submitBtn.addEventListener('click', async () => {
            const subject = document.getElementById('aptSubject').value.trim();
            if (!subject) {
                alert('Please enter an appointment title.');
                document.getElementById('aptSubject').focus();
                return;
            }

            const formData = new FormData();
            formData.append('subject', document.getElementById('aptSubject').value);
            formData.append('location', document.getElementById('aptLocation').value);
            formData.append('description', document.getElementById('aptDescription').value);
            formData.append('start_at', document.getElementById('aptStart').value);
            formData.append('end_at', document.getElementById('aptEnd').value);

            // Toggles (handle potential removal from UI)
            const remTog = document.getElementById('aptReminderToggle');
            const briefTog = document.getElementById('aptAutoBriefToggle');
            formData.append('reminder_minutes', (remTog && remTog.checked) ? 15 : 15);
            formData.append('auto_send_brief', (briefTog && briefTog.checked) ? 1 : 0);

            selectedInvitedUsers.forEach(id => formData.append('invited_user_ids[]', id));

            // Handle Custom Visual
            const coverInput = document.getElementById('aptCoverInput');
            if (coverInput && coverInput.files[0]) {
                formData.append('cover_image', coverInput.files[0]);
            }

            submitBtn.disabled = true;
            submitBtn.innerText = 'Creating...';

            try {
                const res = await fetch('/appointments/api/store', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                const errDisp = document.getElementById('aptActionError');
                if (errDisp) errDisp.style.display = 'none';

                let data;
                const contentType = res.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    data = await res.json();
                }

                if (!res.ok) {
                    if (data) {
                        // Handle Laravel validation errors or custom conflict messages
                        let msg = data.message || "Error.";
                        if (data.errors) {
                            msg = Object.values(data.errors)[0][0];
                        }
                        if (errDisp) {
                            errDisp.innerText = msg;
                            errDisp.style.display = 'block';
                        } else {
                            alert(msg);
                        }
                    } else {
                        const text = await res.text();
                        console.error("Store Error:", res.status, text.substring(0, 200));
                        if (errDisp) {
                            errDisp.innerText = "Error creating appointment. Please try again.";
                            errDisp.style.display = 'block';
                        }
                    }
                    return;
                }

                if (data && data.ok) {
                    if (window.IS_CREATION_PAGE || !window.calendar) {
                        window.location.href = '/appointments/' + data.id;
                    } else {
                        switchAptView('calendar');
                        if (calendar) calendar.refetchEvents();
                    }
                }
            } catch (err) {
                console.error(err);
                alert("Network error.");
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Create Appointment';
            }
        });
    }

    // Update displays on time changes
    const timeInps = [document.getElementById('aptStart'), document.getElementById('aptEnd')];
    timeInps.forEach(input => input && input.addEventListener('change', () => {
        updateAptPillDisplays();
        checkAptConflicts();
    }));

    function showAptDetails(event) {
        const props = event.extendedProps;
        if (props.type === 'holiday') return;

        // Get the real appointment ID
        let id = event.id;
        if (props.type === 'invitation' && props.appointment_id) {
            id = props.appointment_id;
        } else if (typeof id === 'string') {
            id = id.replace('inv-', '');
        }

        window.location.href = '/appointments/' + id;
    }

    function resetAptForm() {
        document.getElementById('aptStoreForm').reset();
        document.getElementById('aptDescription').value = '';
        document.getElementById('aptSelectedUsers').innerHTML = '';
        selectedInvitedUsers = [];
        document.getElementById('aptConflictAlert').style.display = 'none';
    }
});
