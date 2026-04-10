@extends('layouts.admin')

@section('title', 'Admin - Professional Titles')

@push('styles')
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
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.admin-header h1 {
    font-size: 1.6rem;
    font-weight: 600;
    margin: 0;
    color: var(--text);
}
.admin-body {
    padding: 24px;
    max-width: 1000px;
    margin: 0 auto;
    width: 100%;
}

.table-responsive {
    overflow-x: auto;
    padding: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

table th, table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid var(--border);
}

table th {
    background-color: var(--input-bg);
    font-weight: 600;
}

.btn-primary {
    background: #3b82f6;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
    text-decoration: none;
    display: inline-block;
}
.btn-primary:hover {
    background: #2563eb;
}

.btn-secondary {
    background: #eef2ff;
    color: #4f46e5;
    padding: 6px 12px;
    border: 1px solid #c7d2fe;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
}
.btn-secondary:hover {
    background: #e0e7ff;
}

.btn-danger {
    background: #fef2f2;
    color: #ef4444;
    border: 1px solid #fecaca;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-danger:hover {
    background: #fee2e2;
}

.alert-success {
    padding: 15px;
    background: rgba(46, 204, 113, 0.1);
    color: #2ecc71;
    border: 1px solid rgba(46, 204, 113, 0.3);
    border-radius: 8px;
    margin: 20px;
}
.alert-error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.4);
    color: #fca5a5;
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin: 20px;
    font-size: 0.9rem;
}

.upload-card {
    background: var(--input-bg);
    border: 1px dashed var(--border);
    border-radius: 8px;
    padding: 20px;
    margin: 20px;
}

.form-group {
    margin-bottom: 15px;
}
.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}
.form-control {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid var(--border);
    background: var(--panel);
    color: var(--text);
}
</style>
@endpush

@section('content')

<main class="dash">

  <div class="admin-body">
    <section class="admin-main">
      <div class="admin-container">
        
        <div class="admin-header">
            <h1>Professional Titles</h1>
        </div>

        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
            </div>
        @endif

        <div class="upload-card">
            <h3>Add New Professional Title</h3>
            <p style="font-size: 0.9rem; margin-bottom: 15px; color: var(--text-muted);">
                Add a new title (e.g., Psychiatrist, Therapist) that doctors can select when registering.
            </p>
            <form action="{{ route('admin.professional-titles.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">Title Name</label>
                    <input type="text" name="name" id="name" class="form-control" required placeholder="e.g. Clinical Psychologist">
                </div>
                <button type="submit" class="btn-primary">Add Title</button>
            </form>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Title Name</th>
                        <th>Created On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($titles as $title)
                        <tr>
                            <td><span style="background: rgba(124, 58, 237, 0.1); color: var(--brand, #7c3aed); padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 0.85rem;">{{ $title->name }}</span></td>
                            <td>{{ $title->created_at->format('M d, Y') }}</td>
                            <td style="display:flex; gap:10px;">
                                <button type="button" class="btn-secondary" onclick="editTitle({{ $title->id }}, '{{ addslashes($title->name) }}')">Edit</button>
                                <form action="{{ route('admin.professional-titles.destroy', $title->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this title?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 20px;">No professional titles added yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

      </div>
    </section>
  </div>

</main>

<!-- Hidden Edit Form -->
<form id="editForm" method="POST" style="display:none;">
    @csrf
    @method('PUT')
    <input type="hidden" name="name" id="editNameInput">
</form>

<script>
function editTitle(id, currentName) {
    let newName = prompt("Edit Professional Title:", currentName);
    if (newName !== null && newName.trim() !== "" && newName !== currentName) {
        let form = document.getElementById('editForm');
        form.action = `/admin/professional-titles/${id}`;
        document.getElementById('editNameInput').value = newName.trim();
        form.submit();
    }
}
</script>

@endsection
