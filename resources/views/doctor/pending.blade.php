@extends('layouts.dashboard')

@section('title', 'Application Pending - AskDocPH')

@push('styles')
<style>
.pending-container {
    max-width: 600px;
    margin: 80px auto;
    padding: 40px;
    background: var(--panel);
    border-radius: 16px;
    border: 1px solid var(--border);
    box-shadow: 0 8px 30px rgba(0, 0, 0, .06);
    color: var(--text);
    text-align: center;
}
.icon-box {
    width: 80px;
    height: 80px;
    background: rgba(243, 156, 18, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: #f39c12;
}
.icon-box i {
    width: 40px;
    height: 40px;
}
.pending-container h1 {
    font-size: 2rem;
    margin-bottom: 15px;
    color: var(--text);
}
.pending-container p {
    color: var(--muted);
    font-size: 1.1rem;
    margin-bottom: 30px;
    line-height: 1.6;
}
.btn-back {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 24px;
    background: var(--brand, #2563eb);
    border-radius: 8px;
    color: white;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}
.btn-back:hover {
    filter: brightness(0.9);
}
</style>
@endpush

@section('content')

<main class="dash">

  <div class="dash-body" style="display: block;">
    <div class="pending-container">
        <div class="icon-box">
            <i data-lucide="clock"></i>
        </div>
        <h1>Application Pending</h1>
        <p>Your application to become a doctor is currently under review. Our administrators will review your submitted credentials shortly. Thank you for your patience!</p>
        
        <a href="{{ route('user.dashboard') }}" class="btn-back">
            <i data-lucide="arrow-left" style="display: inline-block; vertical-align: middle; margin-right: 8px; width: 18px; height: 18px;"></i>
            Back to Dashboard
        </a>
    </div>
  </div>
</main>
@endsection
