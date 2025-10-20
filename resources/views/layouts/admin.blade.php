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
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-dark bg-dark shadow-sm">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ url('/admin') }}">
                    <i class="fas fa-globe-americas me-2"></i>{{ config('app.name', 'Experiencia Intercultural') }}
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
                        <div class="sidebar-heading">
                            Principal
                        </div>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin') ? 'active' : '' }}" href="{{ url('/admin') }}">
                                    <i class="fas fa-tachometer-alt"></i> Tablero
                                </a>
                            </li>
                        </ul>
                        
                        <div class="sidebar-heading">
                            Gestión de Usuarios
                        </div>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/users*') && !request()->has('program_type') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                    <i class="fas fa-user-shield"></i> Administradores
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/agents*') ? 'active' : '' }}" href="{{ route('admin.agents.index') }}">
                                    <i class="fas fa-user-tie"></i> Agentes
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/participants*') ? 'active' : '' }}" href="{{ route('admin.participants.index') }}">
                                    <i class="fas fa-user-graduate"></i> Participantes
                                </a>
                            </li>
                        </ul>
                        
                        <div class="sidebar-heading">
                            Programas IE
                        </div>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/ie-programs*') ? 'active' : '' }}" href="{{ route('admin.ie-programs.index') }}">
                                    <i class="fas fa-graduation-cap"></i> Programas IE
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/applications*') && request()->input('program_type') == 'IE' ? 'active' : '' }}" href="{{ route('admin.applications.index', ['program_type' => 'IE']) }}">
                                    <i class="fas fa-file-alt"></i> Solicitudes IE
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/documents*') && request()->input('program_type') == 'IE' ? 'active' : '' }}" href="{{ route('admin.documents.index', ['program_type' => 'IE']) }}">
                                    <i class="fas fa-folder-open"></i> Documentos IE
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/participants*') && request()->input('program_category') == 'IE' ? 'active' : '' }}" href="{{ route('admin.participants.index', ['program_category' => 'IE']) }}">
                                    <i class="fas fa-users"></i> Participantes IE
                                </a>
                            </li>
                        </ul>
                        
                        <div class="sidebar-heading">
                            Programas YFU
                        </div>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/yfu-programs*') ? 'active' : '' }}" href="{{ route('admin.yfu-programs.index') }}">
                                    <i class="fas fa-globe-americas"></i> Programas YFU
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/applications*') && request()->input('program_type') == 'YFU' ? 'active' : '' }}" href="{{ route('admin.applications.index', ['program_type' => 'YFU']) }}">
                                    <i class="fas fa-file-alt"></i> Solicitudes YFU
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/documents*') && request()->input('program_type') == 'YFU' ? 'active' : '' }}" href="{{ route('admin.documents.index', ['program_type' => 'YFU']) }}">
                                    <i class="fas fa-folder-open"></i> Documentos YFU
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/participants*') && request()->input('program_category') == 'YFU' ? 'active' : '' }}" href="{{ route('admin.participants.index', ['program_category' => 'YFU']) }}">
                                    <i class="fas fa-users"></i> Participantes YFU
                                </a>
                            </li>
                        </ul>
                        
                        <div class="sidebar-heading">
                            General
                        </div>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/applications*') ? 'active' : '' }}" href="{{ url('/admin/applications') }}">
                                    <i class="fas fa-file-alt"></i> Todas las Solicitudes
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/assignments*') ? 'active' : '' }}" href="{{ route('admin.assignments.index') }}">
                                    <i class="fas fa-user-plus"></i> Asignaciones de Programas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/documents*') && !request()->has('program_type') ? 'active' : '' }}" href="{{ route('admin.documents.index') }}">
                                    <i class="fas fa-file-pdf"></i> Documentos
                                </a>
                            </li>
                        </ul>
                        
                        <div class="sidebar-heading">
                            Recompensas
                        </div>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/rewards*') ? 'active' : '' }}" href="{{ url('/admin/rewards') }}">
                                    <i class="fas fa-award"></i> Recompensas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/redemptions*') ? 'active' : '' }}" href="{{ url('/admin/redemptions') }}">
                                    <i class="fas fa-gift"></i> Canjes
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/points*') ? 'active' : '' }}" href="{{ url('/admin/points') }}">
                                    <i class="fas fa-coins"></i> Puntos
                                </a>
                            </li>
                        </ul>
                        
                        <div class="sidebar-heading">
                            Work & Travel
                        </div>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/sponsors*') ? 'active' : '' }}" href="{{ route('admin.sponsors.index') }}">
                                    <i class="fas fa-handshake"></i> Sponsors
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/host-companies*') ? 'active' : '' }}" href="{{ route('admin.host-companies.index') }}">
                                    <i class="fas fa-building"></i> Empresas Host
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/job-offers*') ? 'active' : '' }}" href="#">
                                    <i class="fas fa-briefcase"></i> Ofertas Laborales
                                </a>
                            </li>
                        </ul>
                        
                        <div class="sidebar-heading">
                            Facturación
                        </div>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/invoices*') ? 'active' : '' }}" href="{{ route('admin.invoices.index') }}">
                                    <i class="fas fa-file-invoice"></i> Facturas
                                </a>
                            </li>
                        </ul>
                        
                        <div class="sidebar-heading">
                            Herramientas
                        </div>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/bulk-import*') ? 'active' : '' }}" href="{{ route('admin.bulk-import.index') }}">
                                    <i class="fas fa-file-import"></i> Importación Masiva
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/activity-logs*') ? 'active' : '' }}" href="{{ route('admin.activity-logs.index') }}">
                                    <i class="fas fa-history"></i> Registro de Auditoría
                                </a>
                            </li>
                        </ul>
                        
                        <div class="sidebar-heading">
                            Configuración
                        </div>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/settings/general*') ? 'active' : '' }}" href="{{ url('/admin/settings/general') }}">
                                    <i class="fas fa-cogs"></i> General
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/settings/whatsapp*') ? 'active' : '' }}" href="{{ url('/admin/settings/whatsapp') }}">
                                    <i class="fab fa-whatsapp text-success"></i> WhatsApp
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/currencies*') ? 'active' : '' }}" href="{{ url('/admin/currencies') }}">
                                    <i class="fas fa-dollar-sign"></i> Valores (Monedas)
                                </a>
                            </li>
                        </ul>

                        <div class="sidebar-heading">
                            Finanzas
                        </div>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/finance') ? 'active' : '' }}" href="{{ url('/admin/finance') }}">
                                    <i class="fas fa-chart-line"></i> Panel Financiero
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/finance/payments*') ? 'active' : '' }}" href="{{ url('/admin/finance/payments') }}">
                                    <i class="fas fa-money-bill-wave"></i> Pagos
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/finance/report*') ? 'active' : '' }}" href="{{ url('/admin/finance/report') }}">
                                    <i class="fas fa-file-invoice-dollar"></i> Informes
                                </a>
                            </li>
                        </ul>
                        
                        <div class="sidebar-heading">
                            Soporte
                        </div>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/support*') ? 'active' : '' }}" href="{{ url('/admin/support') }}">
                                    <i class="fas fa-ticket-alt"></i> Tickets
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/notifications*') ? 'active' : '' }}" href="{{ url('/admin/notifications') }}">
                                    <i class="fas fa-bell"></i> Notificaciones
                                </a>
                            </li>
                        </ul>
                        
                        <div class="sidebar-heading">
                            Reportes
                        </div>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/reports') && !request()->is('admin/reports/*') ? 'active' : '' }}" href="{{ url('/admin/reports') }}">
                                    <i class="fas fa-chart-line"></i> Tablero Financiero
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/reports/programs*') ? 'active' : '' }}" href="{{ url('/admin/reports/programs') }}">
                                    <i class="fas fa-chart-bar"></i> Por Programas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/reports/currencies*') ? 'active' : '' }}" href="{{ url('/admin/reports/currencies') }}">
                                    <i class="fas fa-coins"></i> Por Monedas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/reports/monthly*') ? 'active' : '' }}" href="{{ url('/admin/reports/monthly') }}">
                                    <i class="fas fa-calendar-alt"></i> Mensuales
                                </a>
                            </li>
                            <hr class="my-2">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/reports/applications*') ? 'active' : '' }}" href="{{ url('/admin/reports/applications') }}">
                                    <i class="fas fa-file-alt"></i> Solicitudes
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/reports/users*') ? 'active' : '' }}" href="{{ url('/admin/reports/users') }}">
                                    <i class="fas fa-users"></i> Usuarios
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/reports/rewards*') ? 'active' : '' }}" href="{{ url('/admin/reports/rewards') }}">
                                    <i class="fas fa-gift"></i> Recompensas
                                </a>
                            </li>
                        </ul>
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
