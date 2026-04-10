@extends('layouts.dashboard')

@section('title', 'Edit Group – ' . $group->name)

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/groups.css') }}">
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
@endpush

@section('content')
<main class="dash">
  <div class="dash-body">
    @include('partials.sidebar', ['active' => 'groups'])

    <main class="groups-main">
        <div style="margin-bottom: 24px;">
            <a href="{{ route('groups.show', $group->id) }}" class="chip-btn" style="display: inline-flex; align-items: center; gap: 8px; background: var(--panel); border: 1px solid var(--border); padding: 10px 18px; border-radius: 12px; color: var(--text); font-weight: 600; text-decoration: none; transition: all 0.2s;">
                <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Back to Group
            </a>
        </div>

        <div class="creation-card">
            <div class="creation-header">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <h1>Edit Support Group</h1>
                        <p>Updating details for <strong>{{ $group->name }}</strong></p>
                    </div>
                </div>
            </div>

            <div class="creation-body">
                <form action="{{ route('groups.update', $group->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group" style="margin-bottom: 24px;">
                        <label>Group Name <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $group->name) }}" required placeholder="e.g. Chronic Pain Warriors" style="width:100%; padding:16px; border:1px solid var(--border); background:var(--input-bg); color:var(--text); border-radius:14px; font-size:16px; transition:all 0.2s;">
                        @error('name') <p style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-group" style="margin-bottom: 24px;">
                        <label>Description <span style="color:var(--danger);">*</span></label>
                        <textarea name="description" required rows="6" placeholder="Describe the purpose of this group and who it's for..." style="width:100%; padding:16px; border:1px solid var(--border); background:var(--input-bg); color:var(--text); border-radius:14px; font-size:16px; resize:vertical; transition:all 0.2s;">{{ old('description', $group->description) }}</textarea>
                        @error('description') <p style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-group" style="margin-bottom: 40px;">
                        <label>Guidelines <span style="font-weight:400; color:var(--muted); text-transform:none; letter-spacing:0;">(Optional)</span></label>
                        <textarea name="guidelines" rows="6" placeholder="List some rules for members (e.g. No medical advice, Be respectful...)" style="width:100%; padding:16px; border:1px solid var(--border); background:var(--input-bg); color:var(--text); border-radius:14px; font-size:16px; resize:vertical; transition:all 0.2s;">{{ old('guidelines', $group->guidelines) }}</textarea>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 16px; padding-top: 32px; border-top: 1px solid var(--border);">
                        <a href="{{ route('groups.show', $group->id) }}" class="btn secondary" style="padding:14px 28px; border-radius:14px; font-weight:700; background:var(--hover); color:var(--text); border:1px solid var(--border); text-decoration:none; display:inline-flex; align-items:center;">Cancel</a>
                        <button type="submit" class="btn primary" style="background:linear-gradient(135deg, #7c3aed, #4f46e5); color:#fff; border:none; padding:14px 40px; border-radius:14px; font-weight:700; box-shadow:0 10px 20px rgba(124,58,237,0.3); cursor:pointer;">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Note: Cover photo editing is handled separately via the "Edit Cover" dropdown on the show page --}}
    </main>
  </div>
</main>
@endsection
