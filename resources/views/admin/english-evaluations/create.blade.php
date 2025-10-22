@extends('layouts.admin')

@section('title', 'Registrar Evaluación de Inglés')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-plus-circle"></i> Registrar Nueva Evaluación
        </h1>
        <a href="{{ route('admin.english-evaluations.index') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <h5><i class="fas fa-exclamation-triangle"></i> Por favor corrige los siguientes errores:</h5>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Datos de la Evaluación</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.english-evaluations.store') }}" method="POST">
                        @csrf

                        <!-- Selección de Participante -->
                        <div class="form-group">
                            <label for="user_id">Participante *</label>
                            <select name="user_id" id="user_id" class="form-control" required>
                                <option value="">Seleccionar participante...</option>
                                @foreach($participants as $participant)
                                    <option value="{{ $participant->id }}" {{ old('user_id') == $participant->id ? 'selected' : '' }}>
                                        {{ $participant->name }} - {{ $participant->email }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                Solo se muestran participantes con menos de 3 intentos
                            </small>
                        </div>

                        <hr>

                        <!-- Puntaje General -->
                        <div class="form-group">
                            <label for="score">Puntaje General (0-100) *</label>
                            <input type="number" name="score" id="score" class="form-control" 
                                   min="0" max="100" value="{{ old('score') }}" required>
                            <small class="form-text text-muted">
                                El nivel CEFR y la clasificación se calcularán automáticamente
                            </small>
                        </div>

                        <!-- Puntajes por Habilidad (Opcionales) -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Opcional:</strong> Puedes registrar puntajes específicos por habilidad
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="listening_score">
                                        <i class="fas fa-headphones"></i> Listening (0-100)
                                    </label>
                                    <input type="number" name="listening_score" id="listening_score" 
                                           class="form-control" min="0" max="100" value="{{ old('listening_score') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="reading_score">
                                        <i class="fas fa-book-open"></i> Reading (0-100)
                                    </label>
                                    <input type="number" name="reading_score" id="reading_score" 
                                           class="form-control" min="0" max="100" value="{{ old('reading_score') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="writing_score">
                                        <i class="fas fa-pen"></i> Writing (0-100)
                                    </label>
                                    <input type="number" name="writing_score" id="writing_score" 
                                           class="form-control" min="0" max="100" value="{{ old('writing_score') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="speaking_score">
                                        <i class="fas fa-comments"></i> Speaking (0-100)
                                    </label>
                                    <input type="number" name="speaking_score" id="speaking_score" 
                                           class="form-control" min="0" max="100" value="{{ old('speaking_score') }}">
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Evaluador -->
                        <div class="form-group">
                            <label for="evaluated_by">Evaluador</label>
                            <input type="text" name="evaluated_by" id="evaluated_by" 
                                   class="form-control" value="{{ old('evaluated_by', Auth::user()->name) }}">
                            <small class="form-text text-muted">
                                Nombre del evaluador que realizó la prueba
                            </small>
                        </div>

                        <!-- Notas -->
                        <div class="form-group">
                            <label for="notes">Notas / Observaciones</label>
                            <textarea name="notes" id="notes" class="form-control" rows="4">{{ old('notes') }}</textarea>
                            <small class="form-text text-muted">
                                Observaciones sobre el desempeño del participante
                            </small>
                        </div>

                        <!-- Guía de Referencia -->
                        <div class="card bg-light mb-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Guía de Referencia CEFR</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="list-unstyled mb-0">
                                            <li><strong>C2 (90-100):</strong> Maestría / EXCELLENT</li>
                                            <li><strong>C1 (80-89):</strong> Dominio avanzado / EXCELLENT</li>
                                            <li><strong>B2 (70-79):</strong> Intermedio-alto / GREAT</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul class="list-unstyled mb-0">
                                            <li><strong>B1 (60-69):</strong> Intermedio / GOOD</li>
                                            <li><strong>A2 (40-59):</strong> Básico / INSUFFICIENT</li>
                                            <li><strong>A1 (0-39):</strong> Principiante / INSUFFICIENT</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Registrar Evaluación
                            </button>
                            <a href="{{ route('admin.english-evaluations.index') }}" class="btn btn-secondary">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Calcular promedio automático si se ingresan puntajes por habilidad
    const skillInputs = ['listening_score', 'reading_score', 'writing_score', 'speaking_score'];
    
    skillInputs.forEach(function(inputId) {
        $('#' + inputId).on('input', function() {
            calculateAverage();
        });
    });
    
    function calculateAverage() {
        let total = 0;
        let count = 0;
        
        skillInputs.forEach(function(inputId) {
            const value = parseInt($('#' + inputId).val());
            if (!isNaN(value) && value >= 0) {
                total += value;
                count++;
            }
        });
        
        if (count === 4) {
            const average = Math.round(total / count);
            $('#score').val(average);
            
            // Mostrar preview del nivel
            showLevelPreview(average);
        }
    }
    
    // Preview del nivel según puntaje
    $('#score').on('input', function() {
        const score = parseInt($(this).val());
        if (!isNaN(score)) {
            showLevelPreview(score);
        }
    });
    
    function showLevelPreview(score) {
        let level = '';
        let classification = '';
        let badgeClass = '';
        
        if (score >= 90) {
            level = 'C2';
            classification = 'EXCELLENT';
            badgeClass = 'success';
        } else if (score >= 80) {
            level = 'C1';
            classification = 'EXCELLENT';
            badgeClass = 'success';
        } else if (score >= 70) {
            level = 'B2';
            classification = 'GREAT';
            badgeClass = 'primary';
        } else if (score >= 60) {
            level = 'B1';
            classification = 'GOOD';
            badgeClass = 'info';
        } else if (score >= 40) {
            level = 'A2';
            classification = 'INSUFFICIENT';
            badgeClass = 'warning';
        } else {
            level = 'A1';
            classification = 'INSUFFICIENT';
            badgeClass = 'warning';
        }
        
        // Mostrar preview
        if ($('#level-preview').length === 0) {
            $('#score').parent().append('<div id="level-preview" class="mt-2"></div>');
        }
        
        $('#level-preview').html(`
            <small>
                <strong>Preview:</strong> 
                <span class="badge badge-${badgeClass}">${level}</span>
                <span class="badge badge-${badgeClass}">${classification}</span>
            </small>
        `);
    }
});
</script>
@endsection
