@extends('layouts.admin')

@section('title', 'Requisitos del Programa: ' . $program->name)

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Requisitos del Programa: {{ $program->name }}</h1>
    
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        @if($program->main_category === 'IE')
            <li class="breadcrumb-item"><a href="{{ route('admin.ie-programs.index') }}">Programas IE</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.ie-programs.show', $program->id) }}">{{ $program->name }}</a></li>
        @else
            <li class="breadcrumb-item"><a href="{{ route('admin.yfu-programs.index') }}">Programas YFU</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.yfu-programs.show', $program->id) }}">{{ $program->name }}</a></li>
        @endif
        <li class="breadcrumb-item active">Requisitos</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-list me-1"></i>
                Requisitos del Programa
            </div>
            <a href="{{ route('admin.programs.requisites.create', $program->id) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Nuevo Requisito
            </a>
        </div>
        <div class="card-body">
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
            
            @if($requisites->isEmpty())
                <div class="alert alert-info">
                    No hay requisitos definidos para este programa. 
                    <a href="{{ route('admin.programs.requisites.create', $program->id) }}" class="alert-link">Crear el primer requisito</a>.
                </div>
            @else
                <table class="table table-bordered table-striped" id="requisitesTable">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Orden</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Obligatorio</th>
                            <th style="width: 150px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="requisitesList">
                        @foreach($requisites as $requisite)
                            <tr data-id="{{ $requisite->id }}">
                                <td class="text-center">
                                    <span class="badge bg-secondary drag-handle" style="cursor: move;" title="Arrastrar para reordenar">
                                        <i class="fas fa-grip-vertical me-1"></i>{{ $requisite->order }}
                                    </span>
                                </td>
                                <td>{{ $requisite->name }}</td>
                                <td>
                                    @if($requisite->type == 'document')
                                        <span class="badge bg-primary">Documento</span>
                                    @elseif($requisite->type == 'action')
                                        <span class="badge bg-success">Acción</span>
                                    @elseif($requisite->type == 'payment')
                                        <span class="badge bg-warning">Pago</span>
                                    @endif
                                </td>
                                <td>
                                    @if($requisite->is_required)
                                        <span class="badge bg-success">Sí</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.programs.requisites.edit', [$program->id, $requisite->id]) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.programs.requisites.destroy', [$program->id, $requisite->id]) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar este requisito?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tabla de requisitos
        const requisitesTable = document.getElementById('requisitesTable');
        if (requisitesTable) {
            const requisitesList = document.getElementById('requisitesList');
            
            // Hacer la lista ordenable
            if (requisitesList) {
                new Sortable(requisitesList, {
                    animation: 150,
                    handle: '.drag-handle',  // Solo arrastrar desde el badge de orden
                    filter: '.btn',          // Ignorar botones
                    preventOnFilter: false,  // Permitir clicks en botones
                    onEnd: function() {
                        // Actualizar el orden en la base de datos
                        const items = requisitesList.querySelectorAll('tr');
                        const requisites = [];
                        
                        items.forEach((item, index) => {
                            requisites.push({
                                id: item.dataset.id,
                                order: index
                            });
                        });
                        
                        // Enviar el nuevo orden al servidor
                        fetch('{{ route("admin.programs.requisites.updateOrder", $program->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ requisites })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Actualizar los números de orden visibles
                                items.forEach((item, index) => {
                                    item.querySelector('td:first-child .badge').textContent = index;
                                });
                            }
                        });
                    }
                });
            }
        }
    });
</script>
@endpush
