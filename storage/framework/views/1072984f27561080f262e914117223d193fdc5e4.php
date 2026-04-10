<?php
    $application = \App\Models\DoctorApplication::where('user_id', $me->id)->latest()->first();
?>

<?php if($application && $application->status === 'pending'): ?>
<div class="panel" style="padding: 40px; text-align: center;">
    <div style="width: 80px; height: 80px; background: rgba(243, 156, 18, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #f39c12;">
        <i data-lucide="clock" style="width: 40px; height: 40px;"></i>
    </div>
    <h2 style="font-size: 2rem; margin-bottom: 15px; color: var(--text);">Application Pending</h2>
    <p style="color: var(--muted); font-size: 1.1rem; line-height: 1.6;">Your application to become a doctor is currently under review. Our administrators will review your submitted credentials and biometric data shortly. Thank you for your patience!</p>
</div>
<?php elseif($me->doctor_status === 'approved' || ($application && $application->status === 'approved')): ?>
<div class="panel" style="padding: 40px; text-align: center;">
    <div style="width: 80px; height: 80px; background: rgba(46, 204, 113, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #2ecc71;">
        <i data-lucide="check-circle" style="width: 40px; height: 40px;"></i>
    </div>
    <h2 style="font-size: 2rem; margin-bottom: 15px; color: var(--text);">Application Approved</h2>
    <p style="color: var(--muted); font-size: 1.1rem; line-height: 1.6;">Congratulations! You are officially an approved medical staff member.</p>
    
    <div style="background: var(--panel); border: 1px solid var(--border); border-radius: 12px; padding: 25px; text-align: left; margin-top: 30px;">
        <h3 style="margin-top: 0; font-size: 1.3rem; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="stethoscope" style="color: var(--brand, #7c3aed); width: 24px; height: 24px;"></i>
            Professional Status & Availability
        </h3>
        <p style="color: var(--muted); font-size: 1rem; margin-bottom: 25px;">
            Set your availability to appear in AI recommendations and receive real-time crisis support notifications.
        </p>
        
        <div style="padding: 15px 20px; background: rgba(124,58,237,0.05); border-radius: 12px; border: 1px solid rgba(124,58,237,0.15); display: flex; align-items: flex-start; gap: 12px;">
            <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(124,58,237,0.1); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i data-lucide="sparkles" style="width:16px; height:16px; color:var(--brand, #7c3aed);"></i>
            </div>
            <div>
                <span style="display: block; font-weight: 700; font-size: 14px; color: var(--text); margin-bottom: 2px;">Automatic AI & Crisis Referral</span>
                <span style="font-size: 12px; color: var(--muted); line-height: 1.4;">
                    Since your application is <strong>Approved</strong>, you are automatically eligible for AI referrals and help requests whenever you are active on the platform (within the last 15 minutes). No manual toggle required.
                </span>
            </div>
        </div>
    </div>
</div>
<?php elseif($me->doctor_status === 'rejected' || ($application && $application->status === 'rejected')): ?>
<div id="rejectionFeedback">
    <div class="panel" style="padding: 40px; text-align: center; border-left: 6px solid #ef4444; background: rgba(239, 68, 68, 0.02);">
        <div style="width: 80px; height: 80px; background: rgba(239, 68, 68, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #ef4444;">
            <i data-lucide="x-circle" style="width: 40px; height: 40px;"></i>
        </div>
        <h2 style="font-size: 2rem; margin-bottom: 15px; color: var(--text);">Application Update</h2>
        
        <?php if($application && $application->admin_notes): ?>
            <div style="background: var(--panel); border: 1px solid var(--border); padding: 20px; border-radius: 12px; margin: 25px auto; max-width: 500px; text-align: left;">
                <strong style="display: block; font-size: 0.8rem; text-transform: uppercase; color: var(--muted); margin-bottom: 8px;">Administrator Feedback:</strong>
                <p style="font-style: italic; color: var(--text); line-height: 1.5;">"<?php echo e($application->admin_notes); ?>"</p>
            </div>
        <?php endif; ?>

        <p style="color: var(--muted); font-size: 1.1rem; line-height: 1.6; margin-bottom: 30px;">
            Unfortunately, your application was not approved at this time. Please address the feedback above and submit a new petition to re-apply.
        </p>

        <button type="button" onclick="showReapplyForm()" class="share-btn" style="margin: 0 auto; display: inline-flex; align-items: center; gap: 10px; padding: 14px 28px; font-size: 1rem; border-radius: 10px; cursor: pointer;">
            <i data-lucide="refresh-ccw" style="width: 20px; height: 20px;"></i>
            Petition to Re-apply Now
        </button>
    </div>
</div>

<div id="reapplicationForm" style="display: none;">
    <div class="panel" style="padding: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <div>
                <h2 style="margin-bottom: 5px;">Re-apply for Medical Staff</h2>
                <p class="muted">Update your credentials based on the feedback provided.</p>
            </div>
            <button type="button" onclick="hideReapplyForm()" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                <i data-lucide="x" style="width: 16px; height: 16px;"></i> Cancel
            </button>
        </div>
        
        <?php echo $__env->make('profile._application_form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
</div>
<?php else: ?>
<div class="panel" style="padding: 30px;">
    <h2 style="margin-bottom: 10px;">Apply for Medical Staff</h2>
    <p class="muted" style="margin-bottom: 25px;">Submit your professional titles, documents, and biometric verification to join our platform as a doctor.</p>
    
    <?php echo $__env->make('profile._application_form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</div>
<?php endif; ?>

<script>
    function showReapplyForm() {
        const feedback = document.getElementById('rejectionFeedback');
        const formArea = document.getElementById('reapplicationForm');
        if(feedback && formArea) {
            feedback.style.display = 'none';
            formArea.style.display = 'block';
            window.scrollTo({ top: formArea.offsetTop - 100, behavior: 'smooth' });
        }
    }
    
    function hideReapplyForm() {
        const feedback = document.getElementById('rejectionFeedback');
        const formArea = document.getElementById('reapplicationForm');
        if(feedback && formArea) {
            feedback.style.display = 'block';
            formArea.style.display = 'none';
            window.scrollTo({ top: feedback.offsetTop - 100, behavior: 'smooth' });
        }
    }
</script>
<?php /**PATH C:\websystem\resources\views/profile/_application.blade.php ENDPATH**/ ?>