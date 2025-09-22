<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Laravel Logbook')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            height: 100vh;
            background: #343a40;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
        }
        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            background: #f8f9fa;
        }
        .navbar-brand {
            color: white !important;
            font-weight: bold;
        }
        .nav-link {
            color: #adb5bd !important;
        }
        .nav-link:hover, .nav-link.active {
            color: white !important;
            background: #495057;
        }
        .status-2xx { color: #28a745; }
        .status-3xx { color: #ffc107; }
        .status-4xx { color: #fd7e14; }
        .status-5xx { color: #dc3545; }
        .method-get { background: #28a745; }
        .method-post { background: #007bff; }
        .method-put { background: #ffc107; }
        .method-patch { background: #17a2b8; }
        .method-delete { background: #dc3545; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="p-3">
            <h4 class="navbar-brand">
                <i class="fas fa-book"></i> Logbook
            </h4>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link {{ request()->routeIs('logbook.dashboard') ? 'active' : '' }}" 
               href="{{ route('logbook.dashboard') }}">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
            <a class="nav-link {{ request()->routeIs('logbook.tracks') ? 'active' : '' }}" 
               href="{{ route('logbook.tracks') }}">
                <i class="fas fa-list me-2"></i> Request Tracks
            </a>
            <a class="nav-link {{ request()->routeIs('logbook.manage.*') ? 'active' : '' }}" 
               href="{{ route('logbook.manage.index') }}">
                <i class="fas fa-cogs me-2"></i> Management
            </a>
        </nav>
    </div>

    <div class="main-content">
        <div class="container-fluid p-4">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>