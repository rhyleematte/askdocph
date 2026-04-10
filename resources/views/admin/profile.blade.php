@extends('layouts.admin')

@section('title', 'Admin - My Profile')

@push('styles')
<style>
/* Extend/Override default dashboard tokens for admin-specific pages */
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
    margin: 0;
    color: var(--text);
}
.admin-body {
    padding: 24px;
    max-width: 800px;
    margin: 0 auto;
    width: 100%;
}

.profile-form-wrap {
    padding: 30px;
}

.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 8px;
}
.form-control {
    width: 100%;
    padding: 12px 15px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--input-bg);
    color: var(--text);
    font-size: 0.95rem;
    transition: all 0.2s;
}
.form-control:focus {
    border-color: var(--primary);
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

@media(max-width: 600px){
    .form-row {
        grid-template-columns: 1fr;
    }
}

.btn-primary {
    background: #3b82f6; /* explicit blue color */
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}
.btn-primary:hover {
    background: #2563eb; /* darker blue for hover */
}

.alert-success {
    padding: 15px;
    background: rgba(46, 204, 113, 0.1);
    color: #2ecc71;
    border: 1px solid rgba(46, 204, 113, 0.3);
    border-radius: 8px;
    margin-bottom: 20px;
}
.alert-error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.4);
    color: #fca5a5;
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 0.9rem;
}

/* Profile Photo Card Specifics */
.profile-photo-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 40px 20px;
    background: var(--input-bg); /* Provides clear contrast bound */
    border: 2px dashed rgba(150, 150, 150, 0.3); /* More visible dashed border */
    border-radius: 12px;
    margin-bottom: 30px;
}
.profile-photo-display {
    position: relative;
    margin-bottom: 15px;
}
.profile-photo-display img {
    width: 120px;
    height: 120px;
    border-radius: 20px;
    object-fit: cover;
    background: #ffffff;
    border: 3px solid #000000;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}
.btn-update-photo {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--panel);
    color: var(--text);
    border: 1px solid var(--border);
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.btn-update-photo:hover {
    background: var(--input-bg);
}
.btn-remove-photo {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--panel);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.3);
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.btn-remove-photo:hover {
    background: rgba(239, 68, 68, 0.05);
}
</style>
@endpush

@section('content')

<main class="dash">

  <div class="admin-body">
    <section class="admin-main">
      <div class="admin-container">
        
        <div class="admin-header">
            <h1>My Profile</h1>
        </div>

        <div class="profile-form-wrap">
            @if(session('success'))
                <div class="alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert-error">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert-error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
                </div>
            @endif

            {{-- Photo Section --}}
            <div class="profile-photo-card">
                <div class="profile-photo-display">
                    <img src="{{ $avatarUrl }}" alt="Profile Photo" id="avatarPreview">
                </div>
                <div class="profile-photo-info">
                    <h3 style="margin-top:0; margin-bottom: 8px; font-size: 1.15rem; color: var(--text); font-weight: 600;">Profile Picture</h3>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 20px;">PNG, JPG or GIF. Max size of 2MB.</p>
                    
                    <div style="display: flex; gap: 12px; align-items: center; justify-content: center; flex-wrap: wrap;">
                        <form action="{{ route('admin.profile.update.photo') }}" method="POST" enctype="multipart/form-data" id="photoUploadForm" style="margin: 0;">
                            @csrf
                            <input type="file" name="profile_photo" id="profile_photo" accept="image/png, image/jpeg, image/gif" style="display: none;">
                            
                            <button type="button" class="btn-update-photo" id="choosePhotoBtn">
                                <i data-lucide="folder" style="color: #eab308; fill: #eab308; width: 18px; height: 18px;"></i> Update Photo
                            </button>
                            
                            <button type="submit" class="btn-update-photo" id="uploadPhotoBtn" style="display: none; border-color: var(--primary); color: var(--primary);">
                                <i data-lucide="upload-cloud" style="width: 18px; height: 18px;"></i> Save Photo
                            </button>
                        </form>
                        
                        @if($admin->avatar_url)
                        <form action="{{ route('admin.profile.delete.photo') }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" class="btn-remove-photo">
                                <i data-lucide="trash-2" style="color: #9ca3af; width: 18px; height: 18px;"></i> Remove
                            </button>
                        </form>
                        @endif
                    </div>
                    <div id="fileNameDisplay" style="font-size: 0.8rem; color: var(--primary); margin-top: 10px; display: none; font-weight: 500;"></div>
                </div>
            </div>

            <form action="{{ route('admin.profile.update') }}" method="POST">
                @csrf
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="fname">First Name</label>
                        <input type="text" id="fname" name="fname" class="form-control" value="{{ old('fname', $admin->fname) }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="mname">Middle Name</label>
                        <input type="text" id="mname" name="mname" class="form-control" value="{{ old('mname', $admin->mname) }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="lname">Last Name</label>
                    <input type="text" id="lname" name="lname" class="form-control" value="{{ old('lname', $admin->lname) }}" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $admin->email) }}" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" class="form-control">
                            <option value="">Select Gender</option>
                            <option value="male" {{ old('gender', $admin->gender) == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $admin->gender) == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender', $admin->gender) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="bday">Birthday</label>
                        <input type="date" id="bday" name="bday" class="form-control" value="{{ old('bday', $admin->bday ? $admin->bday->format('Y-m-d') : '') }}">
                    </div>
                </div>

                <div style="margin-top: 20px; text-align: right;">
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>

      </div>
    </section>
  </div>

</main>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Profile Photo Upload UX
    const fileInput = document.getElementById('profile_photo');
    const chooseBtn = document.getElementById('choosePhotoBtn');
    const uploadBtn = document.getElementById('uploadPhotoBtn');
    const fileNameDisplay = document.getElementById('fileNameDisplay');
    const avatarPreview = document.getElementById('avatarPreview');

    if (fileInput && chooseBtn) {
        chooseBtn.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                fileNameDisplay.textContent = 'Selected: ' + file.name;
                fileNameDisplay.style.display = 'block';
                uploadBtn.style.display = 'inline-flex';
                
                // Image preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            } else {
                fileNameDisplay.style.display = 'none';
                uploadBtn.style.display = 'none';
            }
        });
    }
});
</script>
@endpush
@endsection
