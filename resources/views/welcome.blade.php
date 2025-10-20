@extends('layouts.app')

@section('title', 'Iniciar Sesión')

@section('styles')
<style>
    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('/images/intercultural-bg.jpg');
        background-size: cover;
        background-position: center;
        padding: 40px 0;
    }
    .login-card {
        background-color: rgba(255, 255, 255, 0.95);
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        overflow: hidden;
    }
    .login-header {
        background-color: #3490dc;
        color: white;
        padding: 20px;
        text-align: center;
        font-weight: 600;
        font-size: 1.5rem;
    }
    .login-body {
        padding: 30px;
    }
    .brand-logo {
        text-align: center;
        margin-bottom: 25px;
    }
    .brand-logo img {
        max-height: 80px;
    }
    .brand-name {
        font-size: 1.8rem;
        color: #3490dc;
        font-weight: 700;
        margin-bottom: 10px;
    }
    .brand-tagline {
        color: #666;
        font-size: 1rem;
        margin-bottom: 30px;
    }
    .form-floating {
        margin-bottom: 20px;
    }
    .btn-login {
        padding: 10px 0;
        font-weight: 600;
        font-size: 1.1rem;
    }
    .login-footer {
        text-align: center;
        margin-top: 20px;
        color: #666;
    }
</style>
@endsection

@section('content')
<div class="login-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="login-card">
                    <div class="login-body">
                        <div class="brand-logo">
                            <!-- Reemplazar con el logo real de la aplicación -->
                            <i class="fas fa-globe-americas fa-4x text-primary"></i>
                            <h1 class="brand-name">Intercultural Experience</h1>
                            <p class="brand-tagline">Panel Administrativo</p>
                        </div>
                        
                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="form-floating mb-3">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="correo@ejemplo.com">
                                <label for="email">Correo Electrónico</label>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Contraseña">
                                <label for="password">Contraseña</label>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    Recordar mis datos
                                </label>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-login">
                                    Iniciar Sesión
                                </button>
                            </div>

                            <div class="mt-3 text-center">
                                <small class="text-muted">
                                    Acceso restringido a administradores
                                </small>
                            </div>
                        </form>
                    </div>
                    <div class="login-footer">
                        &copy; {{ date('Y') }} Intercultural Experience - Todos los derechos reservados
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
@endsection