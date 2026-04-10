@extends('layouts.dashboard')

@section('title', 'Application Rejected - AskDocPH')

@push('styles')
<style>
.rejected-container {
    max-width: 600px;
    margin: 60px auto;
    padding: 0;
    background: var(--panel);
    border-radius: 20px;
    border: 1px solid var(--border);
    box-shadow: 0 10px 40px rgba(0, 0, 0, .08);
    color: var(--text);
    overflow: hidden;
}
.rejected-header {
    background: rgba(239, 68, 68, 0.05);
    padding: 40px;
    text-align: center;
    border-bottom: 1px solid var(--border);
}
.icon-box {
    width: 80px;
    height: 80px;
    background: rgba(239, 68, 68, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: #ef4444;
}
.icon-box i {
    width: 40px;
    height: 40px;
}
.rejected-header h1 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 10px;
    color: var(--text);
}
.rejected-header p {
    color: var(--muted);
    font-size: 1rem;
}
.rejected-body {
    padding: 40px;
}
.feedback-box {
    background: var(--bg);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 30px;
    border: 1px solid var(--border);
    border-left: 5px solid #ef4444;
}
.feedback-box h3 {
    font-size: 0.9rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 12px;
    color: var(--muted);
}
.feedback-text {
    font-size: 1.05rem;
    line-height: 1.6;
    color: var(--text);
    font-style: italic;
}
.actions {
    display: flex;
    flex-direction: column;
    gap: 15px;
}
.btn-reapply {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px 28px;
    background: var(--primary);
    border-radius: 10px;
    color: white;
    text-decoration: none;
    font-weight: 700;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
    font-size: 1rem;
}
.btn-reapply:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}
.btn-back-home {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px;
    color: var(--muted);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.95rem;
}
.btn-back-home:hover {
    color: var(--text);
}
</style>
@endpush

@section('content')

<main class="dash">

  <div class="dash-body" style="display: block;">
    <div class="rejected-container">
        <div class="rejected-header">
            <div class="icon-box">
                <i data-lucide="x-circle"></i>
            </div>
            <h1>Application Update</h1>
            <p>We've reviewed your credentials for the medical staff panel.</p>
        </div>

        <div class="rejected-body">
            @if($application && $application->admin_notes)
                <div class="feedback-box">
                    <h3>Administrator Feedback</h3>
                    <div class="feedback-text">
                        "{{ $application->admin_notes }}"
                    </div>
                </div>
            @endif

            <p style="margin-bottom: 25px; line-height: 1.6; color: var(--text-muted);">
                Unfortunately, based on the findings above, your application was not approved at this time. However, we encourage you to address the feedback and submit a new petition to re-apply.
            </p>

            <div class="actions">
                <form action="{{ route('doctor.apply.reapply') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-reapply">
                        <i data-lucide="refresh-ccw" style="width: 20px; height: 20px;"></i>
                        Petition to Re-apply Now
                    </button>
                </form>

                <a href="{{ route('user.dashboard') }}" class="btn-back-home">
                    <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
                    Return to Dashboard
                </a>
            </div>
        </div>
    </div>
  </div>
</main>
@endsection
