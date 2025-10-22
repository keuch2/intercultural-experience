@extends('layouts.admin')

@section('title', 'Enviar Comunicación Masiva')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-paper-plane"></i> Enviar Comunicación Masiva
        </h1>
        <div>
            <a href="{{ route('admin.communications.templates') }}" class="btn btn-sm btn-info">
                <i class="fas fa-file-alt"></i> Ver Templates
            </a>
            <a href="{{ route('admin.communications.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <form action="{{ route('admin.communications.send') }}" method="POST" id="communicationForm">
        @csrf
        
        <div class="row">
            <!-- Formulario Principal -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Contenido del Mensaje</h6>
                    </div>
                    <div class="card-body">
                        <!-- Template Selector -->
                        <div class="form-group">
                            <label>Usar Template (Opcional)</label>
                            <select id="template-selector" class="form-control">
                                <option value="">-- Seleccionar Template --</option>
                                @foreach($templates as $template)
                                    <option value="{{ json_encode($template) }}">{{ $template['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Subject -->
                        <div class="form-group">
                            <label>Asunto *</label>
                            <input type="text" name="subject" class="form-control" 
                                   placeholder="Ej: Bienvenido al programa {programa}" 
                                   value="{{ old('subject') }}" required>
                            @error('subject')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Message -->
                        <div class="form-group">
                            <label>Mensaje *</label>
                            <textarea name="message" class="form-control" rows="12" 
                                      placeholder="Escribe tu mensaje aquí... Puedes usar variables como {nombre}, {programa}, {nivel_ingles}" 
                                      required>{{ old('message') }}</textarea>
                            @error('message')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Variables Disponibles -->
                        <div class="alert alert-info">
                            <strong><i class="fas fa-info-circle"></i> Variables Disponibles:</strong><br>
                            <code>{nombre}</code> - Nombre del participante<br>
                            <code>{email}</code> - Email del participante<br>
                            <code>{telefono}</code> - Teléfono del participante<br>
                            <code>{programa}</code> - Nombre del programa<br>
                            <code>{categoria_programa}</code> - Categoría (IE/YFU)<br>
                            <code>{nivel_ingles}</code> - Nivel CEFR (si aplica)<br>
                            <code>{puntaje_ingles}</code> - Puntaje de inglés (si aplica)<br>
                            <code>{fecha}</code> - Fecha actual
                        </div>

                        <!-- Preview Button -->
                        <button type="button" class="btn btn-secondary" id="previewBtn">
                            <i class="fas fa-eye"></i> Vista Previa
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sidebar Filtros -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-primary text-white">
                        <h6 class="m-0 font-weight-bold">Seleccionar Destinatarios</h6>
                    </div>
                    <div class="card-body">
                        <!-- Filtro por Programa -->
                        <div class="form-group">
                            <label>Programa</label>
                            <select name="program_filter" id="program_filter" class="form-control">
                                <option value="">Todos</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}">{{ $program->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filtro por Categoría -->
                        <div class="form-group">
                            <label>Categoría</label>
                            <select name="category_filter" id="category_filter" class="form-control">
                                <option value="">Todas</option>
                                <option value="IE">IE</option>
                                <option value="YFU">YFU</option>
                            </select>
                        </div>

                        <!-- Filtro por Estado -->
                        <div class="form-group">
                            <label>Estado de Aplicación</label>
                            <select name="status_filter" id="status_filter" class="form-control">
                                <option value="">Todos</option>
                                <option value="draft">Borrador</option>
                                <option value="submitted">Enviada</option>
                                <option value="under_review">En Revisión</option>
                                <option value="approved">Aprobada</option>
                                <option value="rejected">Rechazada</option>
                            </select>
                        </div>

                        <!-- Filtro por Nivel de Inglés -->
                        <div class="form-group">
                            <label>Nivel de Inglés</label>
                            <select name="english_filter" id="english_filter" class="form-control">
                                <option value="">Todos</option>
                                <option value="A2">A2</option>
                                <option value="B1">B1</option>
                                <option value="B1+">B1+</option>
                                <option value="B2">B2</option>
                                <option value="C1">C1</option>
                                <option value="C2">C2</option>
                            </select>
                        </div>

                        <button type="button" class="btn btn-info btn-block" id="loadRecipientsBtn">
                            <i class="fas fa-search"></i> Buscar Destinatarios
                        </button>

                        <hr>

                        <!-- Contador de Destinatarios -->
                        <div id="recipients-info" class="text-center" style="display: none;">
                            <h4 class="text-primary">
                                <span id="recipients-count">0</span>
                            </h4>
                            <p class="text-muted mb-0">destinatarios encontrados</p>
                        </div>

                        <!-- Lista de Destinatarios -->
                        <div id="recipients-list" style="display: none; max-height: 300px; overflow-y: auto;">
                            <!-- Populated by JavaScript -->
                        </div>

                        <input type="hidden" name="recipient_ids" id="recipient_ids">
                    </div>
                </div>

                <!-- Resumen de Envío -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Resumen</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Destinatarios:</strong> <span id="summary-count">0</span></p>
                        <p><strong>Asunto:</strong> <span id="summary-subject" class="text-muted">-</span></p>
                        <hr>
                        <button type="submit" class="btn btn-success btn-block" id="sendBtn" disabled>
                            <i class="fas fa-paper-plane"></i> Enviar Comunicación
                        </button>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Verifica todo antes de enviar
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal Preview -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vista Previa del Mensaje</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-header bg-light">
                        <strong>Asunto:</strong> <span id="preview-subject"></span>
                    </div>
                    <div class="card-body" style="white-space: pre-wrap;" id="preview-message">
                    </div>
                </div>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle"></i>
                    <strong>Nota:</strong> Las variables como {nombre}, {programa}, etc. se reemplazarán automáticamente por los datos de cada destinatario.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let recipients = [];

    // Template selector
    $('#template-selector').change(function() {
        const template = $(this).val();
        if (template) {
            const data = JSON.parse(template);
            $('input[name="subject"]').val(data.subject);
            $('textarea[name="message"]').val(data.message);
        }
    });

    // Load recipients
    $('#loadRecipientsBtn').click(function() {
        const filters = {
            program_id: $('#program_filter').val(),
            program_category: $('#category_filter').val(),
            application_status: $('#status_filter').val(),
            english_level: $('#english_filter').val(),
        };

        $.ajax({
            url: '{{ route("admin.communications.get-recipients") }}',
            method: 'GET',
            data: filters,
            success: function(response) {
                recipients = response.recipients;
                updateRecipientsList(recipients);
                updateRecipientIds();
                $('#recipients-count').text(response.count);
                $('#summary-count').text(response.count);
                $('#recipients-info').show();
                $('#recipients-list').show();
                $('#sendBtn').prop('disabled', response.count === 0);
            },
            error: function() {
                alert('Error al cargar destinatarios');
            }
        });
    });

    function updateRecipientsList(recipients) {
        const html = recipients.map(r => `
            <div class="border-bottom py-2">
                <small><strong>${r.name}</strong><br>${r.email}</small>
            </div>
        `).join('');
        $('#recipients-list').html(html);
    }

    function updateRecipientIds() {
        const ids = recipients.map(r => r.id);
        $('#recipient_ids').val(JSON.stringify(ids));
    }

    // Update summary
    $('input[name="subject"]').on('input', function() {
        $('#summary-subject').text($(this).val() || '-');
    });

    // Preview
    $('#previewBtn').click(function() {
        const subject = $('input[name="subject"]').val();
        const message = $('textarea[name="message"]').val();
        
        if (!subject || !message) {
            alert('Completa el asunto y mensaje primero');
            return;
        }

        $('#preview-subject').text(subject);
        $('#preview-message').text(message);
        $('#previewModal').modal('show');
    });

    // Form validation
    $('#communicationForm').submit(function(e) {
        if (recipients.length === 0) {
            e.preventDefault();
            alert('Debes seleccionar al menos un destinatario');
            return false;
        }

        if (!confirm(`¿Enviar email a ${recipients.length} destinatario(s)?`)) {
            e.preventDefault();
            return false;
        }

        // Convert recipient_ids to array format for Laravel
        const ids = recipients.map(r => r.id);
        $('#recipient_ids').remove();
        ids.forEach(id => {
            $('<input>').attr({
                type: 'hidden',
                name: 'recipient_ids[]',
                value: id
            }).appendTo('#communicationForm');
        });
    });
});
</script>
@endsection
