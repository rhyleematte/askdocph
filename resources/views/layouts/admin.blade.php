<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin - AskDocPH')</title>
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap">

  <!-- Base + Dashboard CSS -->
  <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/messenger.css') }}">

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>

  <!-- Page-specific styles -->
  @stack('styles')
</head>

<body>

  @php
    $layout_admin = Auth::guard('admin')->user();
    $layout_admin_avatar = $layout_admin->avatar_url ? asset('storage/'.$layout_admin->avatar_url) : asset('assets/img/default.png');
    $layout_admin_name = addslashes($layout_admin->short_name ?: $layout_admin->email);
  @endphp
  <script>
    window.MY_ID = {{ $layout_admin->id }};
    window.MY_AVATAR = "{{ $layout_admin_avatar }}";
    window.MY_NAME = "{{ $layout_admin_name }}";
  </script>
  @include('partials.admin_header')

@yield('content')

<!-- Inject Admin Messenger Drawer -->
@include('partials.admin_messenger')

<!-- Admin Dashboard JS -->
<script src="{{ asset('assets/js/admin-ui.js') }}?v={{ filemtime(public_path('assets/js/admin-ui.js')) }}" defer></script>
<script src="{{ asset('assets/js/admin-spa.js') }}?v={{ filemtime(public_path('assets/js/admin-spa.js')) }}" defer></script>
<script src="{{ asset('assets/js/admin-messenger.js') }}?v={{ filemtime(public_path('assets/js/admin-messenger.js')) }}" defer></script>
<script src="{{ asset('assets/js/admin-notifications.js') }}?v={{ filemtime(public_path('assets/js/admin-notifications.js')) }}" defer></script>
@stack('scripts')
</body>
</html>
