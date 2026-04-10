<div class="panel mini-panel" id="doctorStatusPanel">
    <div class="mini-title"><i data-lucide="stethoscope"></i><span>Doctor Status</span></div>
    <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 10px;">
        <label style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
            <span style="font-size: 14px; color: var(--text-2);">Online Status</span>
            <input type="checkbox" id="doctorOnlineToggle" {{ Auth::user()->is_online ? 'checked' : '' }} style="accent-color: var(--primary);">
        </label>
        <label style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
            <span style="font-size: 14px; color: var(--text-2);">Free for Get Help</span>
            <input type="checkbox" id="doctorFreeToggle" {{ Auth::user()->is_free_to_talk ? 'checked' : '' }} style="accent-color: var(--danger);">
        </label>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const onlineToggle = document.getElementById('doctorOnlineToggle');
    const freeToggle = document.getElementById('doctorFreeToggle');

    function updateStatus(isOnline, isFree) {
        fetch('{{ url("/api/help/toggle-status") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                is_online: isOnline,
                is_free_to_talk: isFree
            })
        })
        .then(res => res.json())
        .then(data => console.log('Status updated', data))
        .catch(err => console.error(err));
    }

    if (onlineToggle) {
        onlineToggle.addEventListener('change', (e) => updateStatus(e.target.checked, freeToggle.checked));
    }
    if (freeToggle) {
        freeToggle.addEventListener('change', (e) => updateStatus(onlineToggle.checked, e.target.checked));
    }
});
</script>
