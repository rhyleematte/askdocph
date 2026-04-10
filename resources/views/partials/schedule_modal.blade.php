<div class="schedule-modal" id="scheduleModal">
    <div class="schedule-backdrop" id="scheduleBackdrop"></div>
    <div class="schedule-content">
        <div class="schedule-header">
            <div class="schedule-title">
                <i data-lucide="calendar"></i>
                <span>Weekly Schedule Management</span>
            </div>
            <button class="schedule-close" id="scheduleCloseBtn">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="schedule-body">
            <p class="schedule-desc">Set your recurring weekly availability. Users and AI will see your active status based on these hours.</p>
            
            <div id="scheduleList" class="schedule-list">
                {{-- Days will be injected here via JS --}}
                <div class="schedule-skeleton">
                    <div class="skeleton-row"></div>
                    <div class="skeleton-row"></div>
                    <div class="skeleton-row"></div>
                </div>
            </div>
        </div>

        <div class="schedule-footer">
            <button class="btn-cancel" id="scheduleCancelBtn">Cancel</button>
            <button class="btn-save" id="scheduleSaveBtn">
                <i data-lucide="save"></i>
                <span>Save Schedule</span>
            </button>
        </div>
    </div>
</div>
