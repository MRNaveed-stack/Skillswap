<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SkillSwap - Learn & Teach')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f1115;
            color: #e5e7eb;
        }
        
        .navbar-brand {
            font-weight: 700;
            background: linear-gradient(90deg, #6366f1, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .navbar {
            background-color: rgba(15, 17, 21, 0.85) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .glass-card {
            background: rgba(31, 41, 55, 0.4);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
        }
        
        .btn-primary {
            background: linear-gradient(90deg, #6366f1, #8b5cf6);
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand fs-4" href="{{ url('/') }}">
                <i class="bi bi-arrow-left-right me-2"></i>SkillSwap
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('skills.*') ? 'active' : '' }}" href="{{ route('skills.index') }}">Find Skills</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('mentors.*') ? 'active' : '' }}" href="{{ route('mentors.index') }}">Mentors</a>
                    </li>
                    <!-- Auth Links -->
                    @guest
                        <li class="nav-item ms-lg-3">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item ms-lg-2">
                            <a class="btn btn-primary rounded-pill px-4 py-2" href="{{ route('register') }}">Sign Up</a>
                        </li>
                    @else
                        @php
                            $unreadNotifications = \App\Models\Notification::where('user_id', Auth::id())->where('is_read', false)->orderBy('created_at', 'desc')->get();
                        @endphp
                        <li class="nav-item dropdown ms-lg-2">
                            <a class="nav-link position-relative" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell fs-5"></i>
                                @if($unreadNotifications->count() > 0)
                                    <span class="position-absolute top-25 start-75 translate-middle p-1 bg-danger border border-light rounded-circle">
                                        <span class="visually-hidden">New alerts</span>
                                    </span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow" aria-labelledby="notifDropdown" style="width: 300px; max-height: 400px; overflow-y: auto;">
                                <li><h6 class="dropdown-header d-flex justify-content-between align-items-center">Notifications 
                                    @if($unreadNotifications->count() > 0)
                                    <form action="{{ route('notifications.readAll') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-link text-decoration-none p-0 text-muted"><small>Mark all read</small></button>
                                    </form>
                                    @endif
                                </h6></li>
                                @forelse($unreadNotifications as $notif)
                                    <li>
                                        <form action="{{ route('notifications.read', $notif->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item py-2 border-bottom border-secondary text-wrap">
                                                <div class="fw-bold small">{{ $notif->title }}</div>
                                                <div class="text-muted small">{{ $notif->body }}</div>
                                                <div class="text-secondary" style="font-size: 0.7rem;">{{ $notif->created_at->diffForHumans() }}</div>
                                            </button>
                                        </form>
                                    </li>
                                @empty
                                    <li><span class="dropdown-item text-muted small py-3 text-center">No new notifications.</span></li>
                                @endforelse
                            </ul>
                        </li>

                        <li class="nav-item dropdown ms-lg-2">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div style="width: 32px; height: 32px; overflow: hidden; margin-right: 8px;">
                                    <x-avatar :user="Auth::user()" size="sm" />
                                </div>
                                {{ Auth::user()->profile->full_name ?? Auth::user()->email }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="{{ route('messages.index') }}">Messages</a></li>
                                <li><a class="dropdown-item" href="{{ route('session-requests.index') }}">Inbox (Requests)</a></li>
                                <li><a class="dropdown-item" href="{{ route('sessions.index') }}">My Sessions</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('user-skills.index') }}">Mentor Dashboard</a></li>
                                <li><a class="dropdown-item" href="{{ route('availability.index') }}">My Availability</a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.show') }}">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger border-0 bg-transparent">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1" style="margin-top: 80px;">
        @yield('content')
    </main>

    <footer class="py-4 mt-auto border-top border-secondary">
        <div class="container text-center text-muted">
            <p class="mb-0">&copy; {{ date('Y') }} SkillSwap. All rights reserved.</p>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    @yield('scripts')
</body>
</html>
