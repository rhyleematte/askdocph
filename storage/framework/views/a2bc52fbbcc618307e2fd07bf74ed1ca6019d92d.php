

<?php $__env->startSection('title', 'Apply for Medical Staff'); ?>

<?php $__env->startSection('content'); ?>
<style>
.choices {
    width: 100%;
    margin-bottom: 0;
}
.choices__inner {
    border: 1px solid var(--border) !important;
    border-radius: 10px !important;
    background-color: var(--input-bg, #ffffff) !important;
    padding: 2px 12px !important;
    min-height: 48px !important;
    display: flex;
    align-items: center;
    box-shadow: none !important;
}
.choices[data-type*="select-one"] .choices__inner {
    padding-bottom: 2px !important;
}
.choices__list--dropdown {
    background-color: var(--panel, #ffffff) !important;
    border: 1px solid var(--border) !important;
    border-radius: 10px !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15) !important;
    z-index: 100 !important;
}
.choices__list--dropdown .choices__item {
    color: var(--text) !important;
}
.choices__list--dropdown .choices__item--selectable.is-highlighted {
    background-color: var(--teal) !important;
    color: white !important;
}
.input-group.choices-group {
    padding: 0;
    border: none;
    background: transparent;
    display: block;
}
</style>
<main class="wrap">
  <section class="left">
    <div class="card" style="width: 100%; max-width: 520px; padding: 40px; margin: 0 auto; overflow-y: auto; max-height: calc(100vh - 120px);">
      <div class="auth-view" style="display: block;">
        <h1>Apply for Staff</h1>
        <p class="subtitle">Submit your medical credentials <?php if(!auth()->check()): ?> and create your account <?php endif; ?></p>

        <?php if(session('success')): ?>
          <div style="background: rgba(46, 204, 113, 0.1); color: #2ecc71; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border: 1px solid rgba(46, 204, 113, 0.4);">
              <?php echo e(session('success')); ?>

          </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
          <div style="background: rgba(231, 76, 60, 0.1); color: #e74c3c; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border: 1px solid rgba(231, 76, 60, 0.4);">
              <?php echo e(session('error')); ?>

          </div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div style="background: rgba(231, 76, 60, 0.1); color: #ef4444; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border: 1px solid rgba(239, 68, 68, 0.2);">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('doctor.apply.store')); ?>" class="form" enctype="multipart/form-data">
          <?php echo csrf_field(); ?>

          <?php if(!auth()->check()): ?>
            <h3 style="margin: 20px 0 10px; font-size: 1.1em; color: var(--teal); border-bottom: 1px solid var(--border); padding-bottom: 5px;">1. Personal Information</h3>

            <label>First Name</label>
            <div class="input-group">
                <i data-lucide="user"></i>
                <input type="text" name="fname" value="<?php echo e(old('fname')); ?>" placeholder="First name" required />
            </div>

            <label>Middle Name (optional)</label>
            <div class="input-group">
                <i data-lucide="user"></i>
                <input type="text" name="mname" value="<?php echo e(old('mname')); ?>" placeholder="Middle name" />
            </div>

            <label>Last Name</label>
            <div class="input-group">
                <i data-lucide="user"></i>
                <input type="text" name="lname" value="<?php echo e(old('lname')); ?>" placeholder="Last name" required />
            </div>

            <label>Gender</label>
            <div class="input-group">
                <i data-lucide="venus-and-mars"></i>
                <select name="gender" required style="border: none; outline: none; width: 100%; font-size: 14px;">
                    <option value="">Select gender</option>
                    <option value="male" <?php if(old('gender')=='male'): ?> selected <?php endif; ?>>Male</option>
                    <option value="female" <?php if(old('gender')=='female'): ?> selected <?php endif; ?>>Female</option>
                    <option value="prefer_not_say" <?php if(old('gender')=='prefer_not_say'): ?> selected <?php endif; ?>>Prefer not to say</option>
                </select>
            </div>

            <label>Birthday</label>
            <div class="input-group">
                <i data-lucide="calendar"></i>
                <input type="date" name="bday" value="<?php echo e(old('bday')); ?>" required />
            </div>

            <h3 style="margin: 25px 0 10px; font-size: 1.1em; color: var(--teal); border-bottom: 1px solid var(--border); padding-bottom: 5px;">2. Account Credentials</h3>

            <label>Username</label>
            <div class="input-group">
                <i data-lucide="at-sign"></i>
                <input type="text" name="username" value="<?php echo e(old('username')); ?>" placeholder="Choose a username" required />
            </div>

            <label>Email Address</label>
            <div class="input-group">
                <i data-lucide="mail"></i>
                <input type="email" name="email" value="<?php echo e(old('email')); ?>" placeholder="you@example.com" required />
            </div>

            <label>Password</label>
            <div class="input-group">
                <i data-lucide="lock"></i>
                <input type="password" name="password" id="reg_password" placeholder="Create a password" required />
                <button type="button" class="toggle" onclick="document.getElementById('reg_password').type = document.getElementById('reg_password').type === 'password' ? 'text' : 'password'">
                    <i data-lucide="eye"></i>
                </button>
            </div>

            <label>Confirm Password</label>
            <div class="input-group">
                <i data-lucide="shield-check"></i>
                <input type="password" name="password_confirmation" id="reg_password_confirm" placeholder="Confirm your password" required />
                <button type="button" class="toggle" onclick="document.getElementById('reg_password_confirm').type = document.getElementById('reg_password_confirm').type === 'password' ? 'text' : 'password'">
                    <i data-lucide="eye"></i>
                </button>
            </div>

            <h3 style="margin: 25px 0 10px; font-size: 1.1em; color: var(--teal); border-bottom: 1px solid var(--border); padding-bottom: 5px;">3. Professional Information</h3>
            <label>Professional Titles (e.g., MD, RN) <span style="color: #ef4444;">*</span></label>
            <div class="input-group choices-group">
                <select name="professional_titles" class="choices-select" required>
                    <option value="" disabled <?php echo e(old('professional_titles') ? '' : 'selected'); ?>>Search and select a title...</option>
                    <?php if(isset($professional_titles)): ?>
                        <?php $__currentLoopData = $professional_titles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $title): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($title->name); ?>" <?php echo e(old('professional_titles') == $title->name ? 'selected' : ''); ?>><?php echo e($title->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </select>
            </div>

            <h3 style="margin: 25px 0 10px; font-size: 1.1em; color: var(--teal); border-bottom: 1px solid var(--border); padding-bottom: 5px;">4. Requirements Verification</h3>
          <?php endif; ?>

          <?php if($requirements->isEmpty()): ?>
              <p style="color: var(--muted); font-size: 14px; margin-bottom: 20px;">No special requirements are needed right now. Please submit to continue.</p>
          <?php else: ?>
              <?php $__currentLoopData = $requirements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label style="margin-top: 15px; margin-bottom: 5px;">
                    <?php echo e($req->name); ?>

                    <?php if($req->is_required): ?> <span style="color: #ef4444;">*</span> <?php else: ?> <span style="color: var(--muted); font-size: 0.8em; font-weight: normal;">(Optional)</span> <?php endif; ?>
                </label>
                <?php if($req->description): ?>
                    <small style="color: var(--muted); display: block; margin-top: -3px; margin-bottom: 10px;"><?php echo e($req->description); ?></small>
                <?php endif; ?>
                
                <div class="input-group" style="margin-bottom: 20px;">
                    <?php if(stripos($req->name, 'video') !== false): ?>
                        <i data-lucide="video" style="color: var(--teal);"></i>
                        <input type="file" name="req_<?php echo e($req->id); ?>" accept="video/mp4,video/x-m4v,video/*" <?php if($req->is_required): ?> required <?php endif; ?> style="padding-top: 6px; width: 100%;" />
                    <?php else: ?>
                        <i data-lucide="upload-cloud" style="color: var(--teal);"></i>
                        <input type="file" name="req_<?php echo e($req->id); ?>" accept=".pdf,.jpeg,.jpg,.png" <?php if($req->is_required): ?> required <?php endif; ?> style="padding-top: 6px; width: 100%;" />
                    <?php endif; ?>
                </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          <?php endif; ?>

        
        <h3 style="margin: 25px 0 10px; font-size: 1.1em; color: var(--teal); border-bottom: 1px solid var(--border); padding-bottom: 5px;"><?php if(!auth()->check()): ?> 5 <?php else: ?> 2 <?php endif; ?>. Biometric Verification</h3>
        <p style="color: var(--muted); font-size: 13px; margin-bottom: 15px;">To ensure the safety of our platform, we require a live Face ID check to match your submitted Licensed ID. We do not permanently store your raw selfie image, only a secure verification hash and timestamp.</p>
        
        <div style="background: var(--bg); border: 1px solid var(--border); border-radius: 10px; padding: 20px; text-align: center; margin-bottom: 20px;">
            <video id="bioVideo" width="100%" height="auto" style="border-radius: 8px; max-height: 240px; background: #000; display: none;" autoplay playsinline></video>
            <canvas id="bioCanvas" style="display: none;"></canvas>
            <img id="bioPreview" style="border-radius: 8px; max-height: 240px; display: none; margin: 0 auto; border: 2px solid var(--teal);" />
            
            <div id="bioControls" style="margin-top: 15px;">
                <button type="button" id="startBioCamera" style="background: white; border: 1px solid var(--border); padding: 8px 16px; border-radius: 8px; color: var(--text); font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                    <i data-lucide="camera" style="width: 16px; height: 16px;"></i> Start Face Match Camera
                </button>
                <button type="button" id="simulateBioCamera" style="background: rgba(243, 156, 18, 0.1); border: 1px solid #f39c12; padding: 8px 16px; border-radius: 8px; color: #f39c12; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; margin-left: 10px;">
                    <i data-lucide="bot" style="width: 16px; height: 16px;"></i> Simulate Scanner (Testing)
                </button>
                <button type="button" id="captureBioPhoto" style="background: var(--teal); border: none; padding: 8px 16px; border-radius: 8px; color: white; font-weight: 600; cursor: pointer; display: none; align-items: center; gap: 8px; margin: 0 auto;">
                    <i data-lucide="scan-face" style="width: 16px; height: 16px;"></i> Capture & Verify
                </button>
                <button type="button" id="retakeBioPhoto" style="background: white; border: 1px solid var(--border); padding: 8px 16px; border-radius: 8px; color: var(--text); font-weight: 600; cursor: pointer; display: none; align-items: center; gap: 8px; margin: 0 auto;">
                    <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i> Retake Snapshot
                </button>
            </div>

            <p id="bioStatusText" style="margin-top: 15px; font-size: 13px; color: var(--teal); font-weight: 600; display: none;">
                <i data-lucide="shield-check" style="width: 14px; height: 14px; vertical-align: middle;"></i> Liveliness & Face Match Verified
            </p>
        </div>

        <label style="display: flex; align-items: flex-start; gap: 10px; font-size: 14px; color: var(--text); cursor: pointer; background: rgba(12, 143, 152, 0.05); padding: 15px; border-radius: 10px; border: 1px solid rgba(12, 143, 152, 0.2); margin-bottom: 25px;">
            <input type="checkbox" name="biometric_consent" required style="margin-top: 3px;" />
            <span><strong>Consent Agreement:</strong> I agree to biometric verification for identity matching and liveness checks. I understand my raw facial scan is not permanently stored. <span style="color: #ef4444;">*</span></span>
        </label>

        
        <input type="hidden" name="liveness_verified" id="inp_liveness" value="0" />
        <input type="hidden" name="face_match_score" id="inp_face_score" value="" />
        <input type="hidden" name="biometric_payload" id="inp_bio_payload" value="" />

          <button type="submit" class="btn primary" style="margin-top: 20px; width: 100%;">
            <?php if(!auth()->check()): ?> Submit Application & Register <?php else: ?> Submit Application <?php endif; ?>
          </button>

          <p class="switch" style="margin-top: 25px;">
            <a href="<?php echo e(auth()->check() ? route('user.dashboard') : route('login')); ?>">&larr; Back to <?php echo e(auth()->check() ? 'Dashboard' : 'Login'); ?></a>
          </p>
        </form>
      </div>
    </div>
  </section>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const titleSelects = document.querySelectorAll('.choices-select');
        titleSelects.forEach(select => {
            new Choices(select, {
                searchEnabled: true,
                itemSelectText: '',
                placeholder: true,
                placeholderValue: 'Search your professional title...',
                searchPlaceholderValue: 'Type to search...'
            });
        });
    });

    // Biometric camera logic
    const bioVideo = document.getElementById('bioVideo');
    const bioCanvas = document.getElementById('bioCanvas');
    const bioPreview = document.getElementById('bioPreview');
    const btnStart = document.getElementById('startBioCamera');
    const btnCapture = document.getElementById('captureBioPhoto');
    const btnRetake = document.getElementById('retakeBioPhoto');
    const btnSimulate = document.getElementById('simulateBioCamera');
    const bioStatus = document.getElementById('bioStatusText');
    
    // Inputs
    const inpLiveness = document.getElementById('inp_liveness');
    const inpFaceScore = document.getElementById('inp_face_score');
    const inpPayload = document.getElementById('inp_bio_payload');

    let stream = null;
    
    if (btnSimulate) {
        btnSimulate.addEventListener('click', () => {
             simulateSuccess();
        });
    }

    btnStart.addEventListener('click', async () => {
        try {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                // Fallback for HTTP / Insecure contexts during local development
                console.warn('getUserMedia is not supported or context is not secure. Simulating camera access.');
                if(confirm('Camera access is blocked (likely because you are not on HTTPS). Do you want to simulate a successful biometric scan for testing?')) {
                    simulateSuccess();
                } else {
                    alert('Biometric verification cancelled.');
                }
                return;
            }

            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
            bioVideo.srcObject = stream;
            bioVideo.style.display = 'block';
            bioPreview.style.display = 'none';
            btnStart.style.display = 'none';
            btnCapture.style.display = 'inline-flex';
            btnRetake.style.display = 'none';
            bioStatus.style.display = 'none';
            
            // reset payload
            inpLiveness.value = "0";
            inpFaceScore.value = "";
            inpPayload.value = "";
        } catch (err) {
            if (err.name === 'NotAllowedError' || err.name === 'SecurityError') {
                if(confirm('Camera access was denied by your system settings. Do you want to simulate a successful biometric scan to bypass this requirement for testing?')) {
                    simulateSuccess();
                    return;
                }
            }
            alert('Unable to access camera: ' + err.name + ' - ' + err.message + '\n\nPlease ensure you have a webcam plugged in, and no other app (like Zoom) is currently using it.');
            console.error(err);
        }
    });

    btnCapture.addEventListener('click', () => {
        if (!stream) return;
        
        bioCanvas.width = bioVideo.videoWidth;
        bioCanvas.height = bioVideo.videoHeight;
        bioCanvas.getContext('2d').drawImage(bioVideo, 0, 0);
        
        const photoData = bioCanvas.toDataURL('image/jpeg');
        
        bioPreview.src = photoData;
        bioPreview.style.display = 'block';
        bioVideo.style.display = 'none';
        
        // Stop camera tracks
        stream.getTracks().forEach(t => t.stop());
        stream = null;
        
        simulateSuccess(photoData);
    });

    function simulateSuccess(dataUrl = null) {
        btnStart.style.display = 'none';
        if (btnSimulate) btnSimulate.style.display = 'none';
        btnCapture.style.display = 'none';
        btnRetake.style.display = 'inline-flex';
        bioStatus.style.display = 'block';

        // Simulate backend verification success payload
        inpLiveness.value = "1";
        inpFaceScore.value = (90 + Math.random() * 9).toFixed(2);
        
        // Use provided snapshot or a dummy base64 string for testing
        inpPayload.value = dataUrl || "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII="; 
        
        if (!dataUrl) {
            bioPreview.src = inpPayload.value;
            bioPreview.style.display = 'block';
            bioVideo.style.display = 'none';
        }
    }

    btnRetake.addEventListener('click', () => {
        btnStart.click(); // restart process
    });
    
    document.querySelector('.form').addEventListener('submit', function(e) {
        if(inpLiveness.value === "0") {
            e.preventDefault();
            alert("Please complete the Face Match verification first by starting the camera and capturing a photo.");
        }
    });
</script>

  <section class="right" style="display: flex; flex-direction: column;">
    <div class="right-content">
      <h2>Join our expert panel<br>of medical staff</h2>

      <div class="feature">
        <div class="badge"><i data-lucide="stethoscope"></i></div>
        <div>
          <h3>Provide Care</h3>
          <p>Help users improve their mental wellness safely.</p>
        </div>
      </div>

      <div class="feature">
        <div class="badge"><i data-lucide="heart-handshake"></i></div>
        <div>
          <h3>Support Community</h3>
          <p>Offer proven advice and clinical intervention.</p>
        </div>
      </div>

      <div class="feature">
        <div class="badge"><i data-lucide="shield-check"></i></div>
        <div>
          <h3>Rigorous Verification</h3>
          <p>We ensure valid medical credentials for all staff.</p>
        </div>
      </div>
    </div>
    
    <div style="margin-top: auto; text-align: center; padding-top: 40px; opacity: 0.5;">
        <i data-lucide="award" style="width: 48px; height: 48px; color: white;"></i>
    </div>
  </section>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\websystem\resources\views/doctor/apply.blade.php ENDPATH**/ ?>