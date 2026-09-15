@php
    $stage = $tabData['stage'];
    $groups = $tabData['groups'];
    $entries = $tabData['entries'];
    $checklist = $tabData['checklist'];
    $isCurrent = $tabData['isCurrent'];
    $isPast = $tabData['isPast'];
    $isFirst = $definition->stageIndex($stage->key) === 0;
@endphp

@if(!$isCurrent && !$isPast && $process->status === 'active')
<div class="alert alert-info py-2 px-3 mb-3"><i class="fas fa-info-circle me-1"></i><small><strong>Pendiente:</strong> el participante aún no llegó a la etapa "{{ $stage->label }}".</small></div>
@endif

@if($isFirst)
    @include('admin.program-process.tabs.partials._personal_data')
@endif

@if($tabData['showEnglish'])
    @include('admin.program-process.tabs.partials._english_tests')
@endif

@foreach($groups as $group)
    @include('admin.program-process.tabs.partials._documents', ['group' => $group, 'groupEntries' => $entries->where('group', $group['key'])->values()])
@endforeach

@if($checklist->isNotEmpty() || $tabData['gates']->isNotEmpty())
    @include('admin.program-process.tabs.partials._checklist')
@endif

@include('admin.program-process.tabs.partials._stage_gate')

@if($definition->nextStage($stage->key)?->is_terminal)
    @include('admin.program-process.tabs.partials._finalization')
@endif
