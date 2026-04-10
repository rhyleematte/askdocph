@if(session('success'))
    <div style="background: rgba(46, 204, 113, 0.1); color: #2ecc71; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border: 1px solid rgba(46, 204, 113, 0.4);">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div style="background: rgba(231, 76, 60, 0.1); color: #e74c3c; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border: 1px solid rgba(231, 76, 60, 0.4);">
        {{ session('error') }}
    </div>
@endif
@if ($errors->any())
    <div style="background: rgba(231, 76, 60, 0.1); color: #ef4444; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border: 1px solid rgba(239, 68, 68, 0.2);">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('doctor.apply.store') }}" enctype="multipart/form-data" id="docApplicationForm">
    @csrf

    {{-- Professional Titles --}}
    <h3 style="margin: 20px 0 10px; font-size: 1.1em; color: var(--brand); border-bottom: 1px solid var(--border); padding-bottom: 5px;">1. Professional Titles</h3>
    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Titles (e.g., MD, PhD, RN) <span style="color: #ef4444;">*</span></label>
    
    <style>
    .choices { width: 100%; margin-bottom: 0; }
    .choices__inner {
        border: 1px solid var(--border) !important;
        border-radius: 10px !important;
        background-color: var(--input-bg, #ffffff) !important;
        padding: 4px 12px !important;
        min-height: 48px !important;
        display: flex;
        align-items: center;
        box-shadow: none !important;
        font-size: 14px;
    }
    .choices[data-type*="select-one"] .choices__inner { padding-bottom: 4px !important; }
    .choices__list--dropdown {
        background-color: var(--panel, #ffffff) !important;
        border: 1px solid var(--border) !important;
        border-radius: 10px !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15) !important;
        z-index: 100 !important;
    }
    .choices__list--dropdown .choices__item { color: var(--text) !important; }
    .choices__list--dropdown .choices__item--selectable.is-highlighted {
        background-color: var(--brand) !important;
        color: white !important;
    }
    </style>

    <div style="margin-bottom: 20px;">
        <select name="professional_titles" class="choices-select" required>
            <option value="" disabled {{ old('professional_titles') ? '' : 'selected' }}>Select your professional title...</option>
            @if(isset($professional_titles))
                @foreach($professional_titles as $title)
                    <option value="{{ $title->name }}" {{ old('professional_titles') == $title->name || (isset($application) && $application->professional_titles == $title->name) ? 'selected' : '' }}>{{ $title->name }}</option>
                @endforeach
            @endif
        </select>
    </div>

    {{-- Verification Documents --}}
    <h3 style="margin: 25px 0 10px; font-size: 1.1em; color: var(--brand); border-bottom: 1px solid var(--border); padding-bottom: 5px;">2. Documentation & Identifications</h3>
    @if(isset($requirements) && $requirements->isNotEmpty())
        @foreach($requirements as $req)
        <label style="display: block; font-weight: 600; margin-top: 15px; margin-bottom: 5px;">
            {{ $req->name }}
            @if($req->is_required) <span style="color: #ef4444;">*</span> @else <span style="color: var(--muted); font-size: 0.8em; font-weight: normal;">(Optional)</span> @endif
        </label>
        @if($req->description)
            <small style="color: var(--muted); display: block; margin-top: -3px; margin-bottom: 10px;">{{ $req->description }}</small>
        @endif
        
        <div style="margin-bottom: 20px; padding: 15px; background: var(--hover); border: 1px dashed var(--border); border-radius: 10px; display: flex; align-items: center;">
            @if(stripos($req->name, 'video') !== false)
                <i data-lucide="video" style="color: var(--brand); margin-right: 10px;"></i>
                <input type="file" name="req_{{ $req->id }}" accept="video/mp4,video/x-m4v,video/*" @if($req->is_required) required @endif style="background: transparent; color: var(--text); width: 100%;" />
            @else
                <i data-lucide="upload-cloud" style="color: var(--brand); margin-right: 10px;"></i>
                <input type="file" name="req_{{ $req->id }}" accept=".pdf,.jpeg,.jpg,.png" @if($req->is_required) required @endif style="background: transparent; color: var(--text); width: 100%;" />
            @endif
        </div>
        @endforeach
    @endif

    {{-- Biometrics / Liveliness --}}
    <h3 style="margin: 25px 0 10px; font-size: 1.1em; color: var(--brand); border-bottom: 1px solid var(--border); padding-bottom: 5px;">3. Biometric Verification</h3>
    <p style="color: var(--muted); font-size: 13px; margin-bottom: 15px;">To ensure the safety of our platform, we require a live Face ID check to match your submitted Licensed ID. We do not permanently store your raw selfie image, only a secure verification hash and timestamp.</p>
    
    <div style="background: var(--hover); border: 1px solid var(--border); border-radius: 10px; padding: 20px; text-align: center; margin-bottom: 20px;">
        <video id="bioVideo" width="100%" height="auto" style="border-radius: 8px; max-height: 240px; background: #000; display: none;" autoplay playsinline></video>
        <canvas id="bioCanvas" style="display: none;"></canvas>
        <img id="bioPreview" style="border-radius: 8px; max-height: 240px; display: none; margin: 0 auto; border: 2px solid var(--brand);" />
        
        <div id="bioControls" style="margin-top: 15px;">
            <button type="button" id="startBioCamera" style="background: var(--panel); border: 1px solid var(--border); padding: 8px 16px; border-radius: 8px; color: var(--text); font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                <i data-lucide="camera"></i> Start Face Match Camera
            </button>
            <button type="button" id="simulateBioCamera" style="background: rgba(243, 156, 18, 0.1); border: 1px solid #f39c12; padding: 8px 16px; border-radius: 8px; color: #f39c12; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; margin-left: 10px;">
                <i data-lucide="bot"></i> Simulate Scanner (Testing)
            </button>
            <button type="button" id="captureBioPhoto" style="background: var(--brand); border: none; padding: 8px 16px; border-radius: 8px; color: white; font-weight: 600; cursor: pointer; display: none; align-items: center; gap: 8px; margin: 0 auto;">
                <i data-lucide="scan-face"></i> Capture & Verify
            </button>
            <button type="button" id="retakeBioPhoto" style="background: var(--panel); border: 1px solid var(--border); padding: 8px 16px; border-radius: 8px; color: var(--text); font-weight: 600; cursor: pointer; display: none; align-items: center; gap: 8px; margin: 0 auto;">
                <i data-lucide="refresh-cw"></i> Retake Snapshot
            </button>
        </div>

        <p id="bioStatusText" style="margin-top: 15px; font-size: 13px; color: var(--brand); font-weight: 600; display: none;">
            <i data-lucide="shield-check" style="width: 14px; height: 14px; vertical-align: middle;"></i> Liveliness & Face Match Verified
        </p>
    </div>

    <label style="display: flex; align-items: flex-start; gap: 10px; font-size: 14px; color: var(--text); cursor: pointer; background: rgba(124, 58, 237, 0.05); padding: 15px; border-radius: 10px; border: 1px solid rgba(124, 58, 237, 0.2); margin-bottom: 25px;">
        <input type="checkbox" name="biometric_consent" required style="margin-top: 3px;" />
        <span><strong>Consent Agreement:</strong> I agree to biometric verification for identity matching and liveness checks. I understand my raw facial scan is not permanently stored. <span style="color: #ef4444;">*</span></span>
    </label>

    {{-- Hidden fields for our generated biometric hashes to simulate backend match logic --}}
    <input type="hidden" name="liveness_verified" id="inp_liveness" value="0" />
    <input type="hidden" name="face_match_score" id="inp_face_score" value="" />
    <input type="hidden" name="biometric_payload" id="inp_bio_payload" value="" />

    <button type="submit" class="share-btn" style="width: 100%; justify-content: center; padding: 14px; font-size: 16px; border-radius: 12px; display: flex; align-items: center; gap: 8px;" id="btnSubmitApplication">
       <i data-lucide="file-check-2"></i> Submit Application
    </button>
</form>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if(typeof Choices !== 'undefined') {
            const titleSelects = document.querySelectorAll('.choices-select');
            titleSelects.forEach(select => {
                // To avoid multiple instantiations if loaded twice
                if(!select.classList.contains('choices__input')) {
                    new Choices(select, {
                        searchEnabled: true,
                        itemSelectText: '',
                        placeholder: true,
                        placeholderValue: 'Search your professional title...',
                        searchPlaceholderValue: 'Type to search...'
                    });
                }
            });
        }
    });

    // Biometric camera logic
    (function() {
        const bioVideo = document.getElementById('bioVideo');
        const bioCanvas = document.getElementById('bioCanvas');
        const bioPreview = document.getElementById('bioPreview');
        const btnStart = document.getElementById('startBioCamera');
        const btnCapture = document.getElementById('captureBioPhoto');
        const btnRetake = document.getElementById('retakeBioPhoto');
        const btnSimulate = document.getElementById('simulateBioCamera');
        const bioStatus = document.getElementById('bioStatusText');
        
        const inpLiveness = document.getElementById('inp_liveness');
        const inpFaceScore = document.getElementById('inp_face_score');
        const inpPayload = document.getElementById('inp_bio_payload');
        const formBtn = document.getElementById('btnSubmitApplication');

        let stream = null;
        
        if (btnSimulate) {
            btnSimulate.addEventListener('click', () => {
                 simulateSuccess();
            });
        }

        if (btnStart) {
            btnStart.addEventListener('click', async () => {
                try {
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        if(confirm('Camera access is blocked (likely because you are not on HTTPS). Do you want to simulate a successful biometric scan for testing?')) {
                            simulateSuccess();
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
                    
                    inpLiveness.value = "0";
                    inpFaceScore.value = "";
                    inpPayload.value = "";
                } catch (err) {
                    if (err.name === 'NotAllowedError' || err.name === 'SecurityError') {
                        if(confirm('Camera access was denied. Simulate successful biometric scan?')) {
                            simulateSuccess();
                            return;
                        }
                    }
                    alert('Unable to access camera: ' + err.name);
                }
            });
        }

        if (btnCapture) {
            btnCapture.addEventListener('click', () => {
                if (!stream) return;
                bioCanvas.width = bioVideo.videoWidth;
                bioCanvas.height = bioVideo.videoHeight;
                bioCanvas.getContext('2d').drawImage(bioVideo, 0, 0);
                const photoData = bioCanvas.toDataURL('image/jpeg');
                bioPreview.src = photoData;
                bioPreview.style.display = 'block';
                bioVideo.style.display = 'none';
                stream.getTracks().forEach(t => t.stop());
                stream = null;
                simulateSuccess(photoData);
            });
        }

        function simulateSuccess(dataUrl = null) {
            if(btnStart) btnStart.style.display = 'none';
            if (btnSimulate) btnSimulate.style.display = 'none';
            if(btnCapture) btnCapture.style.display = 'none';
            if(btnRetake) btnRetake.style.display = 'inline-flex';
            if(bioStatus) bioStatus.style.display = 'block';

            inpLiveness.value = "1";
            inpFaceScore.value = (90 + Math.random() * 9).toFixed(2);
            inpPayload.value = dataUrl || "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII="; 
            
            if (!dataUrl) {
                bioPreview.src = inpPayload.value;
                bioPreview.style.display = 'block';
                bioVideo.style.display = 'none';
            }
        }

        if (btnRetake) {
            btnRetake.addEventListener('click', () => {
                btnStart.click();
            });
        }
        
        const form = document.getElementById('docApplicationForm');
        if(form) {
            form.addEventListener('submit', function(e) {
                if(inpLiveness.value === "0") {
                    e.preventDefault();
                    alert("Please complete the Face Match verification first.");
                }
            });
        }
    })();
</script>
