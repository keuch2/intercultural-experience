@extends('layouts.admin')

@section('title', 'Avisos especiales')

@section('content')
@php
    $statusClasses = ['scheduled' => 'info', 'sending' => 'warning', 'sent' => 'success', 'cancelled' => 'secondary'];
@endphp
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Avisos especiales</h1>
            <p class="text-muted mb-0 small">Avisos de IE para grupos de participantes: quedan en Avisos de la app y pueden llegar como notificación push. Se envían al momento o en la fecha programada.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.notification-types.index') }}" class="btn btn-outline-secondary"><i class="fas fa-sliders-h me-1"></i> Configurar notificaciones</a>
            <a href="{{ route('admin.notification-campaigns.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nuevo aviso</a>
        </div>
    </div>


    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card shadow-sm"><div class="card-body">
                <div class="text-muted small">Participantes con la app para push</div>
                <div class="h4 mb-0">{{ $participantsWithDevice }}</div>
                <div class="small text-muted">{{ $devices }} dispositivo(s) registrados</div>
            </div></div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($campaigns->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="fas fa-bullhorn fa-2x mb-2"></i>
                    <p class="mb-0">Todavía no se enviaron avisos especiales.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Aviso</th>
                                <th>Destinatarios</th>
                                <th>Envío</th>
                                <th class="text-center">Push enviadas</th>
                                <th class="text-center">Leídos</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($campaigns as $c)
                                @php $s = $stats[$c->id]; @endphp
                                <tr style="cursor: pointer" onclick="window.location='{{ route('admin.notification-campaigns.show', $c) }}'">
                                    <td>
                                        <div class="fw-semibold">
                                            @if($c->is_important)<span class="badge bg-danger me-1">Importante</span>@endif
                                            {{ $c->title }}
                                        </div>
                                        <div class="small text-muted">{{ \Illuminate\Support\Str::limit($c->message, 80) }}</div>
                                    </td>
                                    <td class="small">
                                        {{ $audience->describe($c->audience ?? []) }}
                                        @if($c->status === 'sent')<br><span class="text-muted">{{ $c->recipients_count }} participante(s)</span>@endif
                                    </td>
                                    <td class="small text-nowrap">
                                        @if($c->status === 'sent'){{ $c->sent_at?->format('d/m/Y H:i') }}
                                        @elseif($c->status === 'scheduled'){{ $c->scheduled_at ? 'Programado: '.$c->scheduled_at->format('d/m/Y H:i') : 'En instantes' }}
                                        @elseif($c->status === 'cancelled')<span class="text-muted">Cancelado</span>
                                        @else Enviando… @endif
                                        <br><span class="text-muted">{{ $c->channel === 'push' ? 'App + push' : 'Solo app' }} · {{ $c->creator?->name }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($c->channel === 'push' && $c->status === 'sent')
                                            {{ $s['push_sent'] }}
                                            @if($s['push_failed'])<span class="text-danger small d-block">{{ $s['push_failed'] }} con error</span>@endif
                                            @if($s['push_pending'])<span class="text-muted small d-block">{{ $s['push_pending'] }} en curso</span>@endif
                                        @else <span class="text-muted">–</span> @endif
                                    </td>
                                    <td class="text-center">
                                        @if($c->status === 'sent'){{ $s['read'] }} <span class="text-muted small">({{ $s['recipients'] ? round($s['read'] * 100 / $s['recipients']) : 0 }}%)</span>
                                        @else <span class="text-muted">–</span> @endif
                                    </td>
                                    <td><span class="badge bg-{{ $statusClasses[$c->status] ?? 'secondary' }}">{{ \App\Models\NotificationCampaign::STATUS_LABELS[$c->status] ?? $c->status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    <div class="mt-3">{{ $campaigns->links() }}</div>
</div>
@endsection
