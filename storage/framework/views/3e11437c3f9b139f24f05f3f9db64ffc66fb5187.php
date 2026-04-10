<?php $__env->startSection('title', 'Admin - Daily Affirmations'); ?>

<?php $__env->startPush('styles'); ?>
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

.admin-header {
    padding: 25px 30px;
    border-bottom: 1px solid var(--border);
}

.admin-header h1 {
    font-size: 1.6rem;
    font-weight: 600;
    margin: 0 0 8px;
    color: var(--text);
}

.admin-header p {
    margin: 0;
    color: var(--muted);
    font-size: 0.95rem;
}

.admin-body {
    padding: 24px;
    max-width: 1180px;
    margin: 0 auto;
    width: 100%;
}

.affirmation-grid {
    display: grid;
    grid-template-columns: 1fr 1.25fr;
    gap: 20px;
    padding: 24px;
}

.info-card,
.form-card {
    background: var(--input-bg);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px;
}

.info-card h3,
.form-card h3 {
    margin: 0 0 10px;
    font-size: 1.05rem;
    color: var(--text);
}

.info-card p,
.form-card p {
    margin: 0 0 14px;
    color: var(--muted);
    font-size: 0.92rem;
    line-height: 1.55;
}

.affirmation-preview {
    background: linear-gradient(135deg, rgba(124, 58, 237, 0.12), rgba(99, 102, 241, 0.08));
    border: 1px solid rgba(124, 58, 237, 0.18);
    border-radius: 14px;
    padding: 18px;
}

.affirmation-preview-quote {
    font-size: 1rem;
    line-height: 1.7;
    font-style: italic;
    color: var(--text);
    margin-bottom: 12px;
}

.affirmation-preview-meta {
    font-size: 0.88rem;
    color: var(--muted);
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 10px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.status-pill.live {
    color: #065f46;
    background: rgba(16, 185, 129, 0.14);
    border: 1px solid rgba(16, 185, 129, 0.24);
}

.status-pill.scheduled {
    color: #92400e;
    background: rgba(245, 158, 11, 0.14);
    border: 1px solid rgba(245, 158, 11, 0.24);
}

.status-pill.draft {
    color: #475569;
    background: rgba(148, 163, 184, 0.14);
    border: 1px solid rgba(148, 163, 184, 0.24);
}

.status-pill.offline {
    color: #4b5563;
    background: rgba(107, 114, 128, 0.14);
    border: 1px solid rgba(107, 114, 128, 0.24);
}

.form-grid {
    display: grid;
    gap: 14px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--text);
}

.form-control {
    width: 100%;
    padding: 12px 14px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: var(--panel);
    color: var(--text);
    font-size: 0.94rem;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 4px;
}

.btn-primary,
.btn-secondary,
.btn-danger,
.btn-success {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    border: none;
}

.btn-primary {
    padding: 10px 18px;
    background: #3b82f6;
    color: #fff;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-secondary {
    padding: 8px 14px;
    background: #eef2ff;
    color: #4338ca;
    border: 1px solid #c7d2fe;
}

.btn-secondary:hover {
    background: #e0e7ff;
}

.btn-success {
    padding: 8px 14px;
    background: rgba(16, 185, 129, 0.12);
    color: #047857;
    border: 1px solid rgba(16, 185, 129, 0.22);
}

.btn-success:hover {
    background: rgba(16, 185, 129, 0.18);
}

.btn-danger {
    padding: 8px 14px;
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.btn-danger:hover {
    background: #fee2e2;
}

@media (prefers-color-scheme: light) {
  .btn-primary {
    background: #2563eb;
    color: #fff;
  }

  .btn-secondary {
    background: #f3f4f6;
    color: #1f2937;
    border-color: #d1d5db;
  }

  .btn-secondary:hover {
    background: #e5e7eb;
  }

  .btn-success {
    background: rgba(16, 185, 129, 0.18);
    color: #065f46;
    border-color: rgba(16, 185, 129, 0.28);
  }

  .btn-success:hover {
    background: rgba(16, 185, 129, 0.25);
  }

  .btn-danger {
    background: #fef2f2;
    color: #991b1b;
    border-color: #fecaca;
  }

  .btn-danger:hover {
    background: #fee2e2;
  }

  .status-pill.offline {
    background: rgba(107, 114, 128, 0.18);
    color: #374151;
    border-color: rgba(107, 114, 128, 0.28);
  }
}

.alert-success {
    padding: 15px;
    background: rgba(46, 204, 113, 0.1);
    color: #16a34a;
    border: 1px solid rgba(46, 204, 113, 0.3);
    border-radius: 8px;
    margin: 20px 24px 0;
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #ef4444;
    padding: 15px;
    border-radius: 8px;
    margin: 20px 24px 0;
}

.table-wrap {
    padding: 0 24px 24px;
}

.table-responsive {
    overflow-x: auto;
    border: 1px solid var(--border);
    border-radius: 14px;
}

.affirmation-table {
    width: 100%;
    border-collapse: collapse;
}

.affirmation-table th,
.affirmation-table td {
    padding: 14px 16px;
    text-align: left;
    border-bottom: 1px solid var(--border);
    vertical-align: top;
}

.affirmation-table th {
    background: var(--input-bg);
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--muted);
}

.affirmation-table td {
    font-size: 0.93rem;
    color: var(--text);
}

.affirmation-table tr:last-child td {
    border-bottom: none;
}

.quote-cell {
    max-width: 360px;
}

.quote-text {
    line-height: 1.55;
    margin-bottom: 6px;
}

.quote-meta {
    color: var(--muted);
    font-size: 0.84rem;
}

.live-indicator {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #16a34a;
    font-weight: 700;
    font-size: 0.84rem;
}

.table-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    align-items: center;
    gap: 8px;
}

.table-actions form {
    margin: 0;
}

.table-actions button {
    min-width: 92px;
}

.modal-form-grid {
    display: grid;
    gap: 14px;
    padding: 0 24px 24px;
}

@media (max-width: 960px) {
    .affirmation-grid {
        grid-template-columns: 1fr;
    }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<main class="dash">
  <div class="admin-body">
    <section class="admin-main">
      <div class="admin-container">
        <div class="admin-header">
          <h1>Daily Affirmations</h1>
          <p>Create, schedule, edit, and post the quote shown in the Daily Affirmation panel across the app.</p>
        </div>

        <?php if(session('success')): ?>
          <div class="alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
          <div class="alert-error">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <div><?php echo e($error); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
        <?php endif; ?>

        <div class="affirmation-grid">
          <div class="info-card">
            <h3>Current Live Affirmation</h3>
            <p>This is the quote users currently see in the Daily Affirmation card.</p>

            <div class="affirmation-preview">
              <?php if($currentAffirmation): ?>
                <div class="affirmation-preview-quote">"<?php echo e($currentAffirmation->quote); ?>"</div>
                <div class="affirmation-preview-meta">
                  <?php if($currentAffirmation->author): ?>
                    <span>Author: <?php echo e($currentAffirmation->author); ?></span>
                  <?php endif; ?>
                  <span class="status-pill live"><?php echo e($currentAffirmation->display_status); ?></span>
                  <?php if($currentAffirmation->publish_at): ?>
                    <span>Posted: <?php echo e($currentAffirmation->publish_at->format('M d, Y g:i A')); ?></span>
                  <?php endif; ?>
                </div>
              <?php else: ?>
                <div class="affirmation-preview-quote">"You are worthy of support and belonging. Your journey is unique, and every step forward is progress."</div>
                <div class="affirmation-preview-meta">
                  <span class="status-pill draft">Fallback</span>
                  <span>No published affirmation yet. The app is using the default fallback quote.</span>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <div class="form-card">
            <h3>Add New Affirmation</h3>
            <p>You can save a draft, post it now, or schedule it for a specific day and time.</p>

            <form action="<?php echo e(route('admin.daily-affirmations.store')); ?>" method="POST">
              <?php echo csrf_field(); ?>
              <div class="form-grid">
                <div class="form-group">
                  <label for="quote">Quote</label>
                  <textarea name="quote" id="quote" class="form-control" rows="4" required placeholder="Write the affirmation quote here..."><?php echo e(old('quote')); ?></textarea>
                </div>

                <div class="form-group">
                  <label for="author">Author or Source (Optional)</label>
                  <input type="text" name="author" id="author" class="form-control" value="<?php echo e(old('author')); ?>" placeholder="e.g. AskDocPH, Maya Angelou">
                </div>

                <div class="form-group">
                  <label for="publish_state">Posting Option</label>
                  <select name="publish_state" id="publish_state" class="form-control" onchange="toggleScheduleField(this, 'createScheduleWrap')">
                    <option value="draft" <?php echo e(old('publish_state', 'draft') === 'draft' ? 'selected' : ''); ?>>Save as Draft</option>
                    <option value="publish_now" <?php echo e(old('publish_state') === 'publish_now' ? 'selected' : ''); ?>>Post Today / Post Now</option>
                    <option value="scheduled" <?php echo e(old('publish_state') === 'scheduled' ? 'selected' : ''); ?>>Schedule for Specific Day and Time</option>
                  </select>
                </div>

                <div class="form-group" id="createScheduleWrap" style="display: <?php echo e(old('publish_state') === 'scheduled' ? 'flex' : 'none'); ?>;">
                  <label for="scheduled_for">Schedule Date and Time <span style="font-weight:normal;color:var(--muted);">(in Philippine Time / PHT)</span></label>
                  <input type="datetime-local" name="scheduled_for" id="scheduled_for" class="form-control" value="<?php echo e(old('scheduled_for')); ?>">
                </div>
              </div>

              <div class="form-actions">
                <button type="submit" class="btn-primary">Save Affirmation</button>
              </div>
            </form>
          </div>
        </div>

        <div class="table-wrap">
          <div class="table-responsive">
            <table class="affirmation-table">
              <thead>
                <tr>
                  <th>Quote</th>
                  <th>Status</th>
                  <th>Schedule</th>
                  <th>Created By</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $affirmations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $affirmation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                  <?php
                    $isCurrent = $currentAffirmation && $currentAffirmation->id === $affirmation->id;
                    $state = strtolower($affirmation->display_status);
                    
                    $editState = $state;
                    if ($editState === 'live' || $editState === 'offline') {
                        $editState = 'publish_now';
                    }

                    $editPayload = [
                      'id' => $affirmation->id,
                      'quote' => $affirmation->quote,
                      'author' => $affirmation->author,
                      'publish_state' => $editState,
                      'scheduled_for' => $affirmation->publish_at ? $affirmation->publish_at->format('Y-m-d\\TH:i') : null,
                    ];
                  ?>
                  <tr>
                    <td class="quote-cell">
                      <div class="quote-text">"<?php echo e($affirmation->quote); ?>"</div>
                      <?php if($affirmation->author): ?>
                        <div class="quote-meta">Author: <?php echo e($affirmation->author); ?></div>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="status-pill <?php echo e($state); ?>"><?php echo e($affirmation->display_status); ?></span>
                      <?php if($isCurrent): ?>
                        <div class="live-indicator" style="margin-top: 8px;">
                          <i data-lucide="sparkles" style="width:14px;height:14px;"></i>
                          <span>Currently shown</span>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if($affirmation->publish_at): ?>
                        <?php echo e($affirmation->publish_at->format('M d, Y g:i A')); ?>

                      <?php else: ?>
                        <span class="quote-meta">Not scheduled</span>
                      <?php endif; ?>
                    </td>
                    <td><?php echo e(optional($affirmation->creator)->short_name ?? 'Admin'); ?></td>
                    <td>
                      <div class="table-actions">
                        <?php if($state === 'scheduled'): ?>
                        <form action="<?php echo e(route('admin.daily-affirmations.publish-now', $affirmation)); ?>" method="POST">
                          <?php echo csrf_field(); ?>
                          <button type="submit" class="btn-success">Post Now</button>
                        </form>
                        <?php endif; ?>

                        <button
                          type="button"
                          class="btn-secondary"
                          data-edit='<?php echo json_encode($editPayload, 15, 512) ?>'
                          onclick="openAffirmationEdit(this)"
                        >
                          Edit
                        </button>

                        <form action="<?php echo e(route('admin.daily-affirmations.destroy', $affirmation)); ?>" method="POST" onsubmit="return confirm('Delete this affirmation?');">
                          <?php echo csrf_field(); ?>
                          <?php echo method_field('DELETE'); ?>
                          <button type="submit" class="btn-danger">Delete</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                  <tr>
                    <td colspan="5" style="text-align:center; padding: 24px;">No daily affirmations added yet.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>
  </div>
</main>

<div class="modal-backdrop" id="editAffirmationModal">
  <div class="modal-box" style="max-width: 560px;">
    <div class="modal-header">
      <h2>Edit Daily Affirmation</h2>
      <button class="modal-close" type="button" onclick="closeAffirmationEdit()"><i data-lucide="x"></i></button>
    </div>

    <form id="editAffirmationForm" method="POST">
      <?php echo csrf_field(); ?>
      <?php echo method_field('PUT'); ?>
      <div class="modal-form-grid">
        <div class="form-group">
          <label for="edit_quote">Quote</label>
          <textarea name="quote" id="edit_quote" class="form-control" rows="4" required></textarea>
        </div>

        <div class="form-group">
          <label for="edit_author">Author or Source (Optional)</label>
          <input type="text" name="author" id="edit_author" class="form-control">
        </div>

        <div class="form-group">
          <label for="edit_publish_state">Posting Option</label>
          <select name="publish_state" id="edit_publish_state" class="form-control" onchange="toggleScheduleField(this, 'editScheduleWrap')">
            <option value="draft">Save as Draft</option>
            <option value="publish_now">Post Today / Post Now</option>
            <option value="scheduled">Schedule for Specific Day and Time</option>
          </select>
        </div>

        <div class="form-group" id="editScheduleWrap" style="display:none;">
          <label for="edit_scheduled_for">Schedule Date and Time <span style="font-weight:normal;color:var(--muted);">(in Philippine Time / PHT)</span></label>
          <input type="datetime-local" name="scheduled_for" id="edit_scheduled_for" class="form-control">
        </div>

        <div class="form-actions">
          <button type="button" class="btn-secondary" onclick="closeAffirmationEdit()">Cancel</button>
          <button type="submit" class="btn-primary">Save Changes</button>
        </div>
      </div>
    </form>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function toggleScheduleField(select, wrapId) {
    const wrap = document.getElementById(wrapId);
    if (!wrap) return;
    wrap.style.display = select.value === 'scheduled' ? 'flex' : 'none';
}

function openAffirmationEdit(button) {
    const payload = JSON.parse(button.dataset.edit || '{}');
    const modal = document.getElementById('editAffirmationModal');
    const form = document.getElementById('editAffirmationForm');

    if (!payload.id || !modal || !form) return;

    form.action = '/admin/daily-affirmations/' + payload.id;
    document.getElementById('edit_quote').value = payload.quote || '';
    document.getElementById('edit_author').value = payload.author || '';
    document.getElementById('edit_publish_state').value = payload.publish_state || 'draft';
    document.getElementById('edit_scheduled_for').value = payload.scheduled_for || '';
    toggleScheduleField(document.getElementById('edit_publish_state'), 'editScheduleWrap');
    modal.classList.add('open');

    if (window.lucide) lucide.createIcons();
}

function closeAffirmationEdit() {
    const modal = document.getElementById('editAffirmationModal');
    if (modal) modal.classList.remove('open');
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\websystem\resources\views/admin/daily_affirmations/index.blade.php ENDPATH**/ ?>