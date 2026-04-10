<div class="panel" id="doctorRequestsPanel" style="display: none; border: 2px solid var(--primary); background: var(--hover, #f8fafc);">
    <div class="mini-title" style="color: var(--primary); font-weight: 700;">
        <i data-lucide="alert-circle"></i><span>Urgent: Incoming Help Request</span>
    </div>
    <div id="doctorRequestsList" style="display: flex; flex-direction: column; gap: 10px; margin-top: 10px;">
        <span style="font-size: 13px; color: var(--text-muted);">Checking for requests...</span>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('doctorRequestsList');
    const panel = document.getElementById('doctorRequestsPanel');

    async function loadRequests() {
        try {
            const res = await fetch('<?php echo e(url("/api/help/pending")); ?>');
            if (!res.ok) return;
            const data = await res.json();
            
            if (data.requests && data.requests.length > 0) {
                panel.style.display = 'block';
                list.innerHTML = '';
                data.requests.forEach(req => {
                    const card = document.createElement('div');
                    card.style.cssText = 'padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface-2); display: flex; align-items: center; justify-content: space-between;';
                    card.innerHTML = `
                        <div>
                            <div style="font-weight: bold; font-size: 14px;">${req.user ? req.user.full_name : 'User'}</div>
                            <div style="font-size: 12px; color: var(--text-3);">Needs a ${req.suggested_title}</div>
                        </div>
                        <button class="accept-btn" data-id="${req.id}" style="background: var(--primary); color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px;">Accept</button>
                    `;
                    list.appendChild(card);
                });
                
                list.querySelectorAll('.accept-btn').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        const rid = e.target.getAttribute('data-id');
                        e.target.innerText = 'Accepting...';
                        e.target.disabled = true;
                        
                        try {
                            const acceptRes = await fetch(`<?php echo e(url("/api/help/accept")); ?>/${rid}`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                                    'Content-Type': 'application/json'
                                }
                            });
                            const acceptData = await acceptRes.json();
                            if (acceptData.success) {
                                window.location.href = acceptData.redirect_url;
                            } else {
                                e.target.innerText = 'Failed';
                            }
                        } catch(err) {
                            e.target.innerText = 'Error';
                        }
                    });
                });
            } else {
                panel.style.display = 'none';
            }
        } catch(e) {}
    }

    if (panel) {
        loadRequests();
        setInterval(loadRequests, 10000); // Check every 10 seconds
    }
});
</script>
<?php /**PATH C:\websystem\resources\views/partials/doctor_pending_requests.blade.php ENDPATH**/ ?>