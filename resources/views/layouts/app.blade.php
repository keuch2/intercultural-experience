<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Intercultural Experience') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
    <style>
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }
        .navbar-brand img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="{{ asset('images/ie-icon.png') }}" alt="IE Logo">
                    <span>{{ config('app.name', 'Intercultural Experience') }}</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        <!-- Programs Dropdown -->
                        <li class="nav-item dropdown">
                            <a id="programsDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                <i class="fas fa-globe-americas me-1"></i> {{ __('Programs') }}
                            </a>
                            <div class="dropdown-menu" aria-labelledby="programsDropdown">
                                <a class="dropdown-item" href="{{ route('programs.index') }}">
                                    <i class="fas fa-list me-2"></i> {{ __('All Programs') }}
                                </a>
                                <a class="dropdown-item" href="{{ route('programs.index', ['category' => 'academic']) }}">
                                    <i class="fas fa-graduation-cap me-2"></i> {{ __('Academic') }}
                                </a>
                                <a class="dropdown-item" href="{{ route('programs.index', ['category' => 'volunteer']) }}">
                                    <i class="fas fa-hands-helping me-2"></i> {{ __('Volunteer') }}
                                </a>
                                <a class="dropdown-item" href="{{ route('programs.index', ['category' => 'internship']) }}">
                                    <i class="fas fa-briefcase me-2"></i> {{ __('Internship') }}
                                </a>
                                <a class="dropdown-item" href="{{ route('programs.index', ['category' => 'language']) }}">
                                    <i class="fas fa-language me-2"></i> {{ __('Language') }}
                                </a>
                            </div>
                        </li>
                        
                        @auth
                            <!-- Applications Dropdown -->
                            <li class="nav-item dropdown">
                                <a id="applicationsDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <i class="fas fa-file-alt me-1"></i> {{ __('Applications') }}
                                </a>
                                <div class="dropdown-menu" aria-labelledby="applicationsDropdown">
                                    <a class="dropdown-item" href="{{ route('applications.index') }}">
                                        <i class="fas fa-folder-open me-2"></i> {{ __('My Applications') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('programs.index') }}">
                                        <i class="fas fa-plus-circle me-2"></i> {{ __('New Application') }}
                                    </a>
                                </div>
                            </li>
                            
                            <!-- Rewards Dropdown -->
                            <li class="nav-item dropdown">
                                <a id="rewardsDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <i class="fas fa-award me-1"></i> {{ __('Rewards') }}
                                </a>
                                <div class="dropdown-menu" aria-labelledby="rewardsDropdown">
                                    <a class="dropdown-item" href="{{ route('rewards.index') }}">
                                        <i class="fas fa-gift me-2"></i> {{ __('Available Rewards') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('rewards.index') }}#redemption-history">
                                        <i class="fas fa-history me-2"></i> {{ __('Redemption History') }}
                                    </a>
                                </div>
                            </li>
                            
                            <!-- Support Dropdown -->
                            <li class="nav-item dropdown">
                                <a id="supportDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <i class="fas fa-question-circle me-1"></i> {{ __('Support') }}
                                </a>
                                <div class="dropdown-menu" aria-labelledby="supportDropdown">
                                    <a class="dropdown-item" href="{{ route('support-tickets.index') }}">
                                        <i class="fas fa-ticket-alt me-2"></i> {{ __('My Tickets') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('support-tickets.create') }}">
                                        <i class="fas fa-plus-circle me-2"></i> {{ __('New Ticket') }}
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#">
                                        <i class="fas fa-book me-2"></i> {{ __('Knowledge Base') }}
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        <i class="fas fa-question me-2"></i> {{ __('FAQs') }}
                                    </a>
                                </div>
                            </li>
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">
                                        <i class="fas fa-sign-in-alt me-1"></i> {{ __('Login') }}
                                    </a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">
                                        <i class="fas fa-user-plus me-1"></i> {{ __('Register') }}
                                    </a>
                                </li>
                            @endif
                        @else
                            @if(Auth::user()->role === 'admin')
                                <li class="nav-item">
                                    <a class="nav-link" href="/admin">
                                        <i class="fas fa-tachometer-alt me-1"></i> {{ __('Admin Panel') }}
                                    </a>
                                </li>
                            @endif
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="fas fa-user me-2"></i> {{ __('My Profile') }}
                                    </a>
                                    
                                    <div class="dropdown-divider"></div>
                                    
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt me-2"></i> {{ __('Logout') }}
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
            <div class="container">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
        
        <footer class="bg-light py-4 mt-5">
            <div class="container text-center">
                <p>&copy; {{ date('Y') }} Intercultural Experience. All rights reserved.</p>
            </div>
        </footer>
    </div>
</body>
</html>
