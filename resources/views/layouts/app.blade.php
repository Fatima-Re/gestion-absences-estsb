<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/image_icon.jpeg') }}">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        :root {
            --primary-color-1: #055571;
            --primary-color-2: #077aa2;
            --secondary-color-1: #712105;
            --secondary-color-2: #f1f1f1;
            --secondary-color-3: #a7a29d;
        }

        .header-logo {
            height: 80px;
            width: auto;
            max-width: 200px;
            object-fit: contain;
            padding: 5px;
        }

        .navbar-brand img {
            height: 40px;
            width: auto;
            margin-right: 10px;
        }

        .navbar {
            background-color: var(--primary-color-1) !important;
        }

        .navbar-brand {
            color: var(--secondary-color-2) !important;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .navbar-brand:hover {
            color: var(--secondary-color-2) !important;
            opacity: 0.9;
        }

        .navbar-nav .nav-link {
            color: var(--secondary-color-2) !important;
        }

        .navbar-nav .nav-link:hover {
            color: var(--secondary-color-2) !important;
            opacity: 0.8;
        }

        .navbar-toggler {
            border-color: var(--secondary-color-2);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(241, 241, 241, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        .dropdown-menu {
            border: 1px solid var(--secondary-color-3);
        }

        .dropdown-item:hover {
            background-color: var(--primary-color-2);
            color: var(--secondary-color-2);
        }

        body {
            background-color: var(--secondary-color-2);
        }

        .header-top {
            background-color: var(--secondary-color-2);
            padding: 10px 0;
            border-bottom: 2px solid var(--primary-color-1);
        }

        .header-logo-container {
            display: flex;
            align-items: center;
            padding: 5px 0;
        }

        /* Responsive navigation improvements */
        @media (max-width: 991.98px) {
            .btn-group {
                flex-direction: column;
                width: 100%;
                margin-bottom: 1rem;
            }

            .btn-group .btn {
                margin-bottom: 0.25rem;
                border-radius: 0.375rem !important;
            }

            .navbar-nav .nav-link {
                padding: 0.5rem 1rem;
            }

            .dropdown-menu {
                background-color: var(--primary-color-1);
                border: none;
            }

            .dropdown-item {
                color: var(--secondary-color-2) !important;
                padding: 0.5rem 1rem;
            }

            .dropdown-item:hover {
                background-color: var(--primary-color-2);
            }
        }

        @media (max-width: 575.98px) {
            .header-logo {
                height: 60px;
                max-width: 150px;
            }

            .navbar-brand {
                font-size: 1rem;
            }

            .navbar-brand img {
                height: 30px;
            }
        }

        /* Breadcrumb styling */
        .breadcrumb {
            background-color: transparent;
            padding: 0.5rem 0;
            margin-bottom: 1rem;
        }

        .breadcrumb-item a {
            color: var(--primary-color-1);
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            color: var(--primary-color-2);
            text-decoration: underline;
        }

        .breadcrumb-item.active {
            color: var(--secondary-color-3);
        }

        /* Navigation button improvements */
        .btn-outline-primary {
            border-color: var(--primary-color-1);
            color: var(--primary-color-1);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color-1);
            border-color: var(--primary-color-1);
            color: var(--secondary-color-2);
        }

        .btn-group .btn {
            margin-right: 0.5rem;
        }

        .btn-group .btn:last-child {
            margin-right: 0;
        }
    </style>
</head>
<body>
    <div id="app">
        <!-- Header Logo (Top Left) -->
        <div class="header-top">
            <div class="container">
                <div class="header-logo-container">
                    <img src="{{ asset('images/image_header.jpeg') }}" alt="Header" class="header-logo">
                </div>
            </div>
        </div>

        <nav class="navbar navbar-expand-md navbar-dark shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="{{ asset('images/image_icon.jpeg') }}" alt="Logo" class="d-inline-block align-top">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @auth
                            @if(Auth::user()->isAdmin())
                                <!-- Admin Navigation -->
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                        <i class="fas fa-tachometer-alt me-1"></i>Tableau de bord
                                    </a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="adminUsersDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-users me-1"></i>Utilisateurs
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="adminUsersDropdown">
                                        <li><a class="dropdown-item {{ request()->routeIs('admin.users.index') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Gérer les utilisateurs</a></li>
                                        <li><a class="dropdown-item {{ request()->routeIs('admin.import.index') ? 'active' : '' }}" href="{{ route('admin.import.index') }}">Importer des étudiants</a></li>
                                    </ul>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="adminAcademicDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-graduation-cap me-1"></i>Académique
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="adminAcademicDropdown">
                                        <li><a class="dropdown-item {{ request()->routeIs('admin.groups.index') ? 'active' : '' }}" href="{{ route('admin.groups.index') }}">Groupes</a></li>
                                        <li><a class="dropdown-item {{ request()->routeIs('admin.modules.index') ? 'active' : '' }}" href="{{ route('admin.modules.index') }}">Modules</a></li>
                                        <li><a class="dropdown-item {{ request()->routeIs('admin.sessions.index') ? 'active' : '' }}" href="{{ route('admin.sessions.index') }}">Sessions</a></li>
                                    </ul>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="adminManagementDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-clipboard-list me-1"></i>Gestion
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="adminManagementDropdown">
                                        <li><a class="dropdown-item {{ request()->routeIs('admin.absences.index') ? 'active' : '' }}" href="{{ route('admin.absences.index') }}">Absences</a></li>
                                        <li><a class="dropdown-item {{ request()->routeIs('admin.justifications.index') ? 'active' : '' }}" href="{{ route('admin.justifications.index') }}">Justifications</a></li>
                                        <li><a class="dropdown-item {{ request()->routeIs('admin.statistics.index') ? 'active' : '' }}" href="{{ route('admin.statistics.index') }}">Statistiques</a></li>
                                        <li><a class="dropdown-item {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">Paramètres</a></li>
                                    </ul>
                                </li>
                            @elseif(Auth::user()->isTeacher())
                                <!-- Teacher Navigation -->
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}" href="{{ route('teacher.dashboard') }}">
                                        <i class="fas fa-tachometer-alt me-1"></i>Tableau de bord
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('teacher.schedule.index') ? 'active' : '' }}" href="{{ route('teacher.schedule.index') }}">
                                        <i class="fas fa-calendar-alt me-1"></i>Emploi du temps
                                    </a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="teacherAttendanceDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-user-check me-1"></i>Présence
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="teacherAttendanceDropdown">
                                        <li><a class="dropdown-item {{ request()->routeIs('teacher.attendance.index') ? 'active' : '' }}" href="{{ route('teacher.attendance.index') }}">Historique</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="alert('Sélectionnez une session depuis l\'emploi du temps')">Prendre présence</a></li>
                                    </ul>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="teacherModulesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-book me-1"></i>Modules
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="teacherModulesDropdown">
                                        <li><a class="dropdown-item {{ request()->routeIs('teacher.modules.index') ? 'active' : '' }}" href="{{ route('teacher.modules.index') }}">Mes modules</a></li>
                                        <li><a class="dropdown-item {{ request()->routeIs('teacher.reports.index') ? 'active' : '' }}" href="{{ route('teacher.reports.index') }}">Rapports</a></li>
                                    </ul>
                                </li>
                            @elseif(Auth::user()->isStudent())
                                <!-- Student Navigation -->
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}">
                                        <i class="fas fa-tachometer-alt me-1"></i>Tableau de bord
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('student.schedule.index') ? 'active' : '' }}" href="{{ route('student.schedule.index') }}">
                                        <i class="fas fa-calendar-alt me-1"></i>Emploi du temps
                                    </a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="studentAbsencesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-user-times me-1"></i>Absences
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="studentAbsencesDropdown">
                                        <li><a class="dropdown-item {{ request()->routeIs('student.absences.index') ? 'active' : '' }}" href="{{ route('student.absences.index') }}">Mes absences</a></li>
                                        <li><a class="dropdown-item {{ request()->routeIs('student.absences.statistics') ? 'active' : '' }}" href="{{ route('student.absences.statistics') }}">Statistiques</a></li>
                                        <li><a class="dropdown-item {{ request()->routeIs('student.justifications.index') ? 'active' : '' }}" href="{{ route('student.justifications.index') }}">Justifications</a></li>
                                    </ul>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="studentProfileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-user me-1"></i>Profil
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="studentProfileDropdown">
                                        <li><a class="dropdown-item {{ request()->routeIs('student.profile.show') ? 'active' : '' }}" href="{{ route('student.profile.show') }}">Mon profil</a></li>
                                        <li><a class="dropdown-item {{ request()->routeIs('student.notifications.index') ? 'active' : '' }}" href="{{ route('student.notifications.index') }}">
                                            Notifications
                                            @if(Auth::user()->notifications()->whereNull('read_at')->count() > 0)
                                                <span class="badge bg-danger ms-2">{{ Auth::user()->notifications()->whereNull('read_at')->count() }}</span>
                                            @endif
                                        </a></li>
                                    </ul>
                                </li>
                            @endif
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @include('partials.breadcrumbs')
            @yield('content')
        </main>

        {{-- Global UI Components --}}
        @include('partials.confirmation-modal')
        @include('partials.toast-notifications')
        @include('partials.export-modal')
    </div>
</body>
</html>
