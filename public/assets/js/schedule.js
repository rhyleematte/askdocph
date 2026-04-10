/**
 * SCHEDULE MANAGEMENT SYSTEM JS
 */
document.addEventListener('DOMContentLoaded', () => {
    const scheduleModal = document.getElementById('scheduleModal');
    const scheduleBtn = document.getElementById('headerScheduleBtn');
    const closeBtn = document.getElementById('scheduleCloseBtn');
    const cancelBtn = document.getElementById('scheduleCancelBtn');
    const backdrop = document.getElementById('scheduleBackdrop');
    const saveBtn = document.getElementById('scheduleSaveBtn');
    const scheduleList = document.getElementById('scheduleList');

    if (!scheduleBtn) return; // Only for doctors

    const daysMap = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    // ── Modal Actions ─────────────────────────────────────────────
    function openModal() {
        scheduleModal.classList.add('open');
        document.body.style.overflow = 'hidden';
        loadSchedule();
    }

    function closeModal() {
        scheduleModal.classList.remove('open');
        document.body.style.overflow = '';
    }

    scheduleBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);

    // ── Load Schedule ─────────────────────────────────────────────
    async function loadSchedule() {
        scheduleList.innerHTML = `
            <div class="skeleton-row"></div>
            <div class="skeleton-row"></div>
            <div class="skeleton-row"></div>
        `;

        try {
            const response = await fetch('/doctor/schedule');
            const data = await response.json();
            renderSchedule(data.schedules);
        } catch (err) {
            console.error('Error loading schedule:', err);
            scheduleList.innerHTML = '<div class="error-msg" style="padding: 30px; text-align: center;">Failed to load schedule. Please try again.</div>';
        }
    }

    function renderSchedule(schedules) {
        // Ensure we have a sorted 0-6 array
        const sorted = [...schedules].sort((a, b) => {
            // Monday-Sunday sorting logic
            let dayA = a.day_of_week === 0 ? 7 : a.day_of_week;
            let dayB = b.day_of_week === 0 ? 7 : b.day_of_week;
            return dayA - dayB;
        });

        scheduleList.innerHTML = '';
        sorted.forEach(item => {
            const row = document.createElement('div');
            row.className = `schedule-row ${item.is_active ? 'active' : 'inactive'}`;
            row.dataset.day = item.day_of_week;

            row.innerHTML = `
                <div class="day-label">${daysMap[item.day_of_week]}</div>
                
                <div class="time-inputs">
                    <div class="time-box">
                        <label>Start</label>
                        <input type="time" class="schedule-input start-time" value="${item.start_time.substring(0, 5)}">
                    </div>
                    <div class="time-box">
                        <label>End</label>
                        <input type="time" class="schedule-input end-time" value="${item.end_time.substring(0, 5)}">
                    </div>
                </div>

                <div class="day-toggle ${item.is_active ? 'active' : ''}" title="Enable/Disable this day">
                    <div class="toggle-knob"></div>
                </div>
            `;

            // Toggle Handler
            const toggle = row.querySelector('.day-toggle');
            toggle.addEventListener('click', () => {
                const isActive = toggle.classList.toggle('active');
                row.classList.toggle('active', isActive);
                row.classList.toggle('inactive', !isActive);
            });

            scheduleList.appendChild(row);
        });
        
        if (window.lucide) lucide.createIcons();
    }

    // ── Save Schedule ─────────────────────────────────────────────
    saveBtn.addEventListener('click', async () => {
        const rows = scheduleList.querySelectorAll('.schedule-row');
        const schedules = [];

        rows.forEach(row => {
            schedules.push({
                day_of_week: parseInt(row.dataset.day),
                start_time: row.querySelector('.start-time').value + ':00',
                end_time: row.querySelector('.end-time').value + ':00',
                is_active: row.querySelector('.day-toggle').classList.contains('active')
            });
        });

        saveBtn.disabled = true;
        const originalHtml = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i data-lucide="loader-2" class="spin"></i><span>Saving...</span>';
        if (window.lucide) lucide.createIcons();

        try {
            const response = await fetch('/doctor/schedule/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ schedules })
            });

            const data = await response.json();
            if (data.success) {
                showToast('Schedule saved successfully!');
                closeModal();
            } else {
                showToast('Error saving schedule: ' + (data.message || 'Unknown error'), 'error');
            }
        } catch (err) {
            console.error('Save error:', err);
            showToast('Failed to connect to the server.', 'error');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalHtml;
            if (window.lucide) lucide.createIcons();
        }
    });

    function showToast(msg, type = 'success') {
        const toast = document.getElementById('toast');
        if (!toast) return;
        toast.textContent = msg;
        toast.className = `toast show ${type}`;
        setTimeout(() => toast.classList.remove('show'), 3000);
    }
});
