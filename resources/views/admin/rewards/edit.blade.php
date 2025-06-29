@extends('layouts.admin')

@section('title', 'Editar Recompensa')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Editar Recompensa</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.rewards.index') }}">Recompensas</a></li>
        <li class="breadcrumb-item active">Editar Recompensa</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-edit me-1"></i>
            Información de la Recompensa
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form action="{{ route('admin.rewards.update', $reward->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input class="form-control" id="name" name="name" type="text" placeholder="Nombre de la recompensa" value="{{ old('name', $reward->name) }}" required />
                            <label for="name">Nombre de la Recompensa</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="is_active" name="is_active">
                                <option value="1" {{ old('is_active', $reward->is_active) == 1 ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('is_active', $reward->is_active) == 0 ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            <label for="is_active">Estado</label>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input class="form-control" id="cost" name="cost" type="number" min="1" placeholder="Costo en puntos" value="{{ old('cost', $reward->cost) }}" required />
                            <label for="cost">Costo en Puntos</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input class="form-control" id="stock" name="stock" type="number" min="0" placeholder="Stock disponible" value="{{ old('stock', $reward->stock) }}" />
                            <label for="stock">Stock Disponible</label>
                            <div class="form-text">Deje en blanco para stock ilimitado</div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Descripción</label>
                    <textarea class="form-control" id="description" name="description" rows="5" required>{{ old('description', $reward->description) }}</textarea>
                </div>
                
                <div class="mb-3">
                    <label for="image" class="form-label">Imagen</label>
                    <input class="form-control" id="image" name="image" type="file" accept="image/*" />
                    <div class="form-text">Deje este campo vacío para mantener la imagen actual.</div>
                    
                    @if($reward->image)
                        <div class="mt-2">
                            <p>Imagen actual:</p>
                            <img src="{{ asset('storage/' . $reward->image) }}" alt="{{ $reward->name }}" class="img-thumbnail" style="max-height: 200px;">
                        </div>
                    @endif
                </div>
                
                <div class="mt-4 mb-0">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-block">Guardar Cambios</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Inicializar editor de texto enriquecido para la descripción
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof ClassicEditor !== 'undefined') {
            ClassicEditor
                .create(document.querySelector('#description'))
                .catch(error => {
                    console.error(error);
                });
        }
    });
</script>
@endsection
