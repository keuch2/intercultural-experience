<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Experiencia Intercultural') }} - Panel de Administración</title>

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
            width: 35px;
            height: 35px;
            object-fit: contain;
        }
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #343a40;
            color: #fff;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
            padding: 0.75rem 1rem;
        }
        
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }
        
        .sidebar-heading {
            font-size: 0.75rem;
            text-transform: uppercase;
            padding: 1rem;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .content-wrapper {
            min-height: calc(100vh - 56px);
            background-color: #f8f9fa;
        }
        
        .card-dashboard {
            border-left: 4px solid;
            border-radius: 4px;
        }
        
        .card-primary {
            border-left-color: #4e73df;
        }
        
        .card-success {
            border-left-color: #1cc88a;
        }
        
        .card-info {
            border-left-color: #36b9cc;
        }
        
        .card-warning {
            border-left-color: #f6c23e;
        }
        
        .sidebar-chevron {
            font-size: 0.7rem;
            transition: transform 0.2s ease;
        }
        .collapsed .sidebar-chevron {
            transform: rotate(-90deg);
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-dark bg-dark shadow-sm">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ url('/admin') }}">
                    <img src="{{ asset('images/ie-icon.png') }}" alt="IE Logo">
                    <span>{{ config('app.name', 'Experiencia Intercultural') }}</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                 document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-md-3 col-lg-2 px-0 sidebar">
                    <div class="position-sticky pt-3">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin') && !request()->is('admin/*') ? 'active' : '' }}" href="{{ url('/admin') }}">
                                    <i class="fas fa-tachometer-alt"></i> Tablero
                                </a>
                            </li>
                        </ul>

                        @if(Auth::user()->role != 'finance')
                        <div class="sidebar-heading">
                            Programas
                        </div>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center justify-content-between {{ request()->is('admin/au-pair*') ? '' : 'collapsed' }}" 
                                   data-bs-toggle="collapse" href="#sidebar-aupair" role="button" 
                                   aria-expanded="{{ request()->is('admin/au-pair*') ? 'true' : 'false' }}">
                                    <span><i class="fas fa-child"></i> Au Pair</span>
                                    <i class="fas fa-chevron-down sidebar-chevron"></i>
                                </a>
                                <div class="collapse {{ request()->is('admin/au-pair*') ? 'show' : '' }}" id="sidebar-aupair">
                                    <ul class="nav flex-column ms-3">
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->is('admin/au-pair/perfiles*') ? 'active' : '' }}" href="{{ route('admin.aupair.profiles.index') }}">
                                                <i class="fas fa-user-circle"></i> Perfiles Au Pair
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->is('admin/au-pair/recursos*') ? 'active' : '' }}" href="{{ route('admin.aupair.resources.index') }}">
                                                <i class="fas fa-folder-open"></i> Recursos del Programa
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->is('admin/au-pair/reportes*') ? 'active' : '' }}" href="{{ route('admin.aupair.reports.index') }}">
                                                <i class="fas fa-chart-bar"></i> Informes
                                            </a>
                                        </li>
                                        @php
                                            $auPairProgram = \App\Models\Program::where('subcategory', 'Au Pair')->where('main_category', 'IE')->first();
                                        @endphp
                                        @if($auPairProgram)
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->is('admin/ie-programs/'.$auPairProgram->id.'/edit') ? 'active' : '' }}" href="{{ route('admin.ie-programs.edit', $auPairProgram->id) }}">
                                                <i class="fas fa-cog"></i> Editar Programa
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </li>
                        </ul>

                        <div class="sidebar-heading">
                            Gestión
                        </div>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/participants*') ? 'active' : '' }}" href="{{ route('admin.participants.index') }}">
                                    <i class="fas fa-user-graduate"></i> Participantes
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/finance/payments*') ? 'active' : '' }}" href="{{ url('/admin/finance/payments') }}">
                                    <i class="fas fa-money-bill-wave"></i> Pagos
                                </a>
                            </li>
                        </ul>
                        @endif

                        <div class="sidebar-heading">
                            Sistema
                        </div>
                        <ul class="nav flex-column">
                            @if(Auth::user()->role != 'finance')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                    <i class="fas fa-user-shield"></i> Usuarios
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/activity-logs*') ? 'active' : '' }}" href="{{ route('admin.activity-logs.index') }}">
                                    <i class="fas fa-history"></i> Auditoría
                                </a>
                            </li>
                            @endif
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/currencies*') ? 'active' : '' }}" href="{{ route('admin.currencies.index') }}">
                                    <i class="fas fa-coins"></i> Monedas y Cotizaciones
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}" href="{{ url('/admin/settings') }}">
                                    <i class="fas fa-cogs"></i> Configuración
                                </a>
                            </li>
                        </ul>

                        {{-- Otros módulos ocultos - accesibles para quienes los necesiten --}}
                        @if(Auth::user()->role != 'finance')
                        <div class="sidebar-heading mt-4" style="opacity: 0.5;">
                            <a class="text-decoration-none text-muted small collapsed" data-bs-toggle="collapse" href="#sidebar-otros" role="button" aria-expanded="false">
                                <i class="fas fa-ellipsis-h"></i> Otros módulos
                            </a>
                        </div>
                        <div class="collapse" id="sidebar-otros">
                            <ul class="nav flex-column" style="font-size: 0.85rem;">
                                <li class="nav-item">
                                    <a class="nav-link py-1" href="{{ route('admin.au-pair.dashboard') }}">
                                        <i class="fas fa-child"></i> Au Pair (legacy)
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link py-1" href="{{ url('/admin/applications') }}">
                                        <i class="fas fa-file-alt"></i> Solicitudes
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link py-1" href="{{ route('admin.documents.index') }}">
                                        <i class="fas fa-file-pdf"></i> Documentos
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link py-1" href="{{ url('/admin/finance') }}">
                                        <i class="fas fa-chart-line"></i> Finanzas
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link py-1" href="{{ url('/admin/reports') }}">
                                        <i class="fas fa-chart-bar"></i> Reportes
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link py-1" href="{{ route('admin.invoices.index') }}">
                                        <i class="fas fa-file-invoice"></i> Facturas
                                    </a>
                                </li>
                            </ul>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Main Content -->
                <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 content-wrapper">
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
            </div>
        </div>
    </div>
    
    @stack('scripts')
</body>
</html>
