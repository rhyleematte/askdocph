<?php $__env->startSection('title', 'Create Support Group – AskDocPH'); ?>

<?php $__env->startPush('styles'); ?>
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/groups.css')); ?>">
  <style>
    .creation-card {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid var(--border);
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        overflow: hidden;
        max-width: 800px;
        margin: 0 auto;
    }
    html.theme-dark .creation-card {
        background: #141d2f;
        border-color: rgba(255,255,255,0.1);
        box-shadow: 0 20px 50px rgba(0,0,0,0.3);
    }
    .creation-header {
        padding: 32px;
        border-bottom: 1px solid var(--border);
        background: linear-gradient(to right, rgba(124, 58, 237, 0.03), rgba(79, 70, 229, 0.03));
    }
    .creation-body {
        padding: 32px;
    }
    .creation-header h1 {
        font-size: 24px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 8px;
    }
    .creation-header p {
        color: var(--muted);
        font-size: 15px;
    }
  </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<main class="dash">
  <div class="dash-body">
    <?php echo $__env->make('partials.sidebar', ['active' => 'groups'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <main class="groups-main">
        <div style="margin-bottom: 24px;">
            <a href="<?php echo e(route('groups.index')); ?>" class="chip-btn" style="display: inline-flex; align-items: center; gap: 8px; background: var(--panel); border: 1px solid var(--border); padding: 10px 18px; border-radius: 12px; color: var(--text); font-weight: 600; text-decoration: none; transition: all 0.2s;">
                <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Back to Groups
            </a>
        </div>

        <div class="creation-card">
            <div class="creation-header">
                <h1>Create Support Group</h1>
                <p>Start a new community to support and guide users through their journey.</p>
            </div>

            <div class="creation-body">
                <form action="<?php echo e(route('groups.store')); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    
                    <div style="padding: 16px; background: rgba(124, 58, 237, 0.05); border-radius: 16px; border: 1px solid rgba(124, 58, 237, 0.1); color: var(--text); font-size: 14px; line-height: 1.6; display: flex; gap: 14px; align-items: flex-start; margin-bottom: 32px;">
                        <i data-lucide="info" style="width:20px; height:20px; color:var(--primary); flex-shrink:0; margin-top:2px;"></i>
                        <span>As an approved doctor, you have the privilege of creating and moderating support groups. Be sure to set clear guidelines to keep the community safe and helpful.</span>
                    </div>

                    <div class="form-group" style="margin-bottom: 32px;">
                        <label>Group Cover Photo <span style="font-weight:400; color:var(--muted); text-transform:none; letter-spacing:0;">(Optional)</span></label>
                        <div class="cover-upload-wrapper" id="uploadZone" style="height: 200px;">
                            <input type="file" name="cover_photo" id="groupCover" accept="image/*" style="opacity:0; position:absolute; inset:0; z-index:10; cursor:pointer; width:100%; height:100%;">
                            <div id="coverUploadCTA" style="text-align:center; color:var(--muted); pointer-events:none;">
                                <div style="width:56px; height:56px; border-radius:50%; background:var(--panel); display:flex; align-items:center; justify-content:center; margin:0 auto 16px; box-shadow:0 8px 20px rgba(0,0,0,0.06);">
                                    <i data-lucide="camera" style="width:28px; height:28px; color:var(--primary);"></i>
                                </div>
                                <div style="font-size:16px; font-weight:800; color:var(--text);">Tap to upload group cover</div>
                                <div style="font-size:13px; margin-top:6px; opacity:0.8;">Recommended size: 1200x400px (Max 10MB)</div>
                            </div>
                            <img id="coverPreview" src="" style="display:none; position:absolute; width:100%; height:100%; object-fit:cover; inset:0; z-index:5;">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 24px;">
                        <label>Group Name <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="name" required placeholder="e.g. Chronic Pain Warriors" style="width:100%; padding:16px; border:1px solid var(--border); background:var(--input-bg); color:var(--text); border-radius:14px; font-size:16px; transition:all 0.2s;">
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p style="color:var(--danger); font-size:12px; margin-top:4px;"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="form-group" style="margin-bottom: 24px;">
                        <label>Description <span style="color:var(--danger);">*</span></label>
                        <textarea name="description" required rows="4" placeholder="Describe the purpose of this group and who it's for..." style="width:100%; padding:16px; border:1px solid var(--border); background:var(--input-bg); color:var(--text); border-radius:14px; font-size:16px; resize:vertical; transition:all 0.2s;"></textarea>
                        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p style="color:var(--danger); font-size:12px; margin-top:4px;"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="form-group" style="margin-bottom: 40px;">
                        <label>Guidelines <span style="font-weight:400; color:var(--muted); text-transform:none; letter-spacing:0;">(Optional)</span></label>
                        <textarea name="guidelines" rows="4" placeholder="List some rules for members (e.g. No medical advice, Be respectful, Keep it confidential...)" style="width:100%; padding:16px; border:1px solid var(--border); background:var(--input-bg); color:var(--text); border-radius:14px; font-size:16px; resize:vertical; transition:all 0.2s;"></textarea>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 16px; padding-top: 32px; border-top: 1px solid var(--border);">
                        <a href="<?php echo e(route('groups.index')); ?>" class="btn secondary" style="padding:14px 28px; border-radius:14px; font-weight:700; background:var(--hover); color:var(--text); border:1px solid var(--border); text-decoration:none; display:inline-flex; align-items:center;">Cancel</a>
                        <button type="submit" class="btn primary" style="background:linear-gradient(135deg, #7c3aed, #4f46e5); color:#fff; border:none; padding:14px 40px; border-radius:14px; font-weight:700; box-shadow:0 10px 20px rgba(124,58,237,0.3); cursor:pointer;">Create Support Group</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
  </div>
</main>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const coverInput = document.getElementById('groupCover');
    const coverPreview = document.getElementById('coverPreview');
    const coverUploadCTA = document.getElementById('coverUploadCTA');

    if (coverInput) {
        coverInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    coverPreview.src = e.target.result;
                    coverPreview.style.display = 'block';
                    coverPreview.style.borderRadius = '14px';
                    coverUploadCTA.style.display = 'none';
                }
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.dashboard', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\websystem\resources\views/groups/create.blade.php ENDPATH**/ ?>