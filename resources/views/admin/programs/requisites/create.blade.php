@extends('layouts.admin')

@section('title', 'Crear Requisito - ' . $program->name)

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Crear Requisito</h1>
    
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.programs.index') }}">Programas</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.programs.show', $program->id) }}">{{ $program->name }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.programs.requisites.index', $program->id) }}">Requisitos</a></li>
        <li class="breadcrumb-item active">Crear</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-plus me-1"></i>
            Nuevo Requisito para el Programa
        </div>
        <div class="card-body">
            <form action="{{ route('admin.programs.requisites.store', $program->id) }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Descripción</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="type" class="form-label">Tipo <span class="text-danger">*</span></label>
                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                        <option value="document" {{ old('type') == 'document' ? 'selected' : '' }}>Documento</option>
                        <option value="action" {{ old('type') == 'action' ? 'selected' : '' }}>Acción</option>
                        <option value="payment" {{ old('type') == 'payment' ? 'selected' : '' }}>Pago</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input @error('is_required') is-invalid @enderror" type="checkbox" id="is_required" name="is_required" value="1" {{ old('is_required') ? 'checked' : 'checked' }}>
                        <label class="form-check-label" for="is_required">
                            Requisito obligatorio
                        </label>
                        @error('is_required')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="order" class="form-label">Orden</label>
                    <input type="number" class="form-control @error('order') is-invalid @enderror" id="order" name="order" value="{{ old('order', 0) }}" min="0">
                    @error('order')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-flex justify-content-end">
                    <a href="{{ route('admin.programs.requisites.index', $program->id) }}" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
