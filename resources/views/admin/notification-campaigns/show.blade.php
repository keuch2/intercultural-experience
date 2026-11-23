@extends('layouts.admin')

@section('title', 'Aviso especial')

@section('content')
@php
    $pushLabels = [
        'sent' => ['success', 'Push enviada'], 'pending' => ['info', 'Push en curso'], 'sending' => ['info', 'Push en curso'],
        'failed' => ['danger', 'Push con error'], 'expired' => ['danger', 'Push vencida'], 'no_device' => ['light text-dark border', 'Sin la app'],
        'muted' => ['light text-dark border', 'Silenciado'], 'disabled' => ['light text-dark border', 'Push apagada'],
    ];
    $statusClasses = ['scheduled' => 'info', 'sending' => 'warning', 'sent' => 'success', 'cancelled' => 'secondary'];
@endphp
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="h3 mb-0">
            {{ $campaign->title }}
            <span class="badge bg-{{ $statusClasses[$campaign->status] ?? 'secondary' }} align-middle fs-6">{{ \App\Models\NotificationCampaign::STATUS_LABELS[$campaign->status] ?? $campaign->status }}</span>
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.notification-campaigns.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Volver</a>
            <a href="{{ route('admin.notification-campaigns.create', ['from' => $campaign->id]) }}" class="btn btn-outline-primary"><i class="fas fa-copy me-1"></i> Duplicar</a>
            @if($campaign->isEditable())
                <a href="{{ route('admin.notification-campaigns.edit', $campaign) }}" class="btn btn-primary"><i class="fas fa-edit me-1"></i> Editar</a>
                <form method="POST" action="{{ route('admin.notification-campaigns.cancel', $campaign) }}" onsubmit="return confirm('¿Cancelar el envío programado?')">
                    @csrf
                    <button class="btn btn-outline-danger"><i class="fas fa-ban me-1"></i> Cancelar envío</button>
                </form>
            @endif
        </div>
    </div>


    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    @if($campaign->is_important)<span class="badge bg-danger mb-2">Importante</span>@endif
                    <p class="mb-3" style="white-space: pre-line;">{{ $campaign->message }}</p>
                    <dl class="row small mb-0">
                        <dt class="col-5">Destinatarios</dt><dd class="col-7">{{ $audienceLabel }}</dd>
                        <dt class="col-5">Canal</dt><dd class="col-7">{{ \App\Models\NotificationCampaign::CHANNELS[$campaign->channel] ?? $campaign->channel }}</dd>
                        <dt class="col-5">Al tocarlo</dt><dd class="col-7">{{ $campaign->linkLabel() ?? 'Abre el aviso' }}</dd>
                        <dt class="col-5">Envío</dt>
                        <dd class="col-7">
                            @if($campaign->sent_at){{ $campaign->sent_at->format('d/m/Y H:i') }}
                            @elseif($campaign->status === 'cancelled')Cancelado el {{ $campaign->cancelled_at?->format('d/m/Y H:i') }}
                            @elseif($campaign->scheduled_at)Programado para el {{ $campaign->scheduled_at->format('d/m/Y H:i') }}
                            @else En instantes @endif
                        </dd>
                        <dt class="col-5">Creado por</dt><dd class="col-7">{{ $campaign->creator?->name ?? '—' }} · {{ $campaign->created_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>

            @if($campaign->status === 'sent')
                <div class="row g-3">
                    @foreach([
                        ['Destinatarios', $stats['recipients'], 'primary'], ['Leídos', $stats['read'], 'success'],
                        ['Push enviadas', $stats['push_sent'], 'info'], ['Push con error', $stats['push_failed'], 'danger'],
                        ['Sin la app', $stats['no_device'], 'secondary'], ['Silenciado', $stats['muted'], 'secondary'],
                    ] as [$label, $value, $color])
                        <div class="col-6">
                            <div class="card shadow-sm border-start border-4 border-{{ $color }}"><div class="card-body py-2">
                                <div class="small text-muted">{{ $label }}</div>
                                <div class="h5 mb-0">{{ $value }}</div>
                            </div></div>
                        </div>
                    @endforeach
                </div>
                @if($stats['push_pending'])<p class="small text-muted mt-2">{{ $stats['push_pending'] }} notificación(es) push en curso: salen en el próximo minuto.</p>@endif
            @elseif($estimated !== null)
                <div class="alert alert-info">Hoy el público elegido tiene <strong>{{ $estimated }}</strong> participante(s). La lista final se arma al momento del envío.</div>
            @endif
        </div>

        <div class="col-lg-7">
            @if($campaign->status === 'sent')
                <div class="card shadow-sm">
                    <div class="card-header bg-white"><h5 class="mb-0">Destinatarios</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light"><tr><th>Participante</th><th>Push</th><th>Leído</th></tr></thead>
                                <tbody>
                                    @foreach($recipients as $n)
                                        @php [$cls, $lbl] = $pushLabels[$n->push_status] ?? ['light text-dark border', 'Solo en la app']; @endphp
                                        <tr>
                                            <td>
                                                @if($n->user)<a href="{{ route('admin.participants.show', $n->user_id) }}">{{ $n->user->name }}</a>
                                                <div class="small text-muted">{{ $n->user->email }}</div>@else — @endif
                                            </td>
                                            <td><span class="badge bg-{{ $cls }}">{{ $lbl }}</span></td>
                                            <td>{!! $n->is_read ? '<i class="fas fa-check text-success"></i>' : '<span class="text-muted">No</span>' !!}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="mt-3">{{ $recipients->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
