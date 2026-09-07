<?php

namespace App\Http\Controllers\API\ProgramEngine;

use App\Http\Controllers\API\ProgramEngine\Concerns\ResolvesProgramProcess;
use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramVisaProcess;
use Illuminate\Http\Request;

class VisaController extends Controller
{
    use ResolvesProgramProcess;

    private const RESULT_LABELS = ['pending' => 'Pendiente', 'approved' => 'Aprobada', 'denied' => 'Denegada', 'administrative_process' => 'Proceso administrativo'];

    public function show(Request $request, Program $engineProgram)
    {
        [$process, $err] = $this->resolveProcess($request, $engineProgram);
        if ($err) {
            return $err;
        }
        $visa = $process->visaProcess;
        if (! $visa) {
            return response()->json(['status' => 'success', 'data' => ['has_visa_process' => false, 'message' => 'El proceso de visa aún no fue iniciado por el equipo IE.']]);
        }
        $label = self::RESULT_LABELS[$visa->interview_result] ?? $visa->interview_result;

        return response()->json(['status' => 'success', 'data' => [
            'has_visa_process' => true,
            'progress_pct' => $visa->progress,
            'interview' => [
                'result' => $visa->interview_result, 'result_label' => $label, 'notes' => $visa->interview_result_notes,
                'date' => optional($visa->appointment_date)->toDateString(),
                'time' => $visa->appointment_time ? substr((string) $visa->appointment_time, 0, 5) : null,
                'embassy' => $visa->embassy,
            ],
            'travel' => [
                'departure' => optional($visa->departure_datetime)->toIso8601String(),
                'arrival_usa' => optional($visa->arrival_usa_datetime)->toIso8601String(),
                'flight_info' => $visa->flight_info,
            ],
            'pre_departure' => ['date' => optional($visa->pre_departure_orientation_date)->toDateString(), 'completed' => (bool) $visa->pre_departure_orientation_completed],
            'timeline' => $this->timeline($visa, $label),
        ]]);
    }

    private function timeline(ProgramVisaProcess $visa, string $resultLabel): array
    {
        return [
            ['key' => 'visa_email_sent', 'label' => 'Email de inicio de proceso', 'completed' => (bool) $visa->visa_email_sent],
            ['key' => 'consular_fee_paid', 'label' => 'Pago de tasa consular', 'completed' => (bool) $visa->consular_fee_paid],
            ['key' => 'appointment_scheduled', 'label' => 'Cita consular agendada', 'completed' => (bool) $visa->appointment_scheduled, 'meta' => $visa->appointment_date?->toDateString()],
            ['key' => 'documents_sent_for_appointment', 'label' => 'Documentos enviados para la cita', 'completed' => (bool) $visa->documents_sent_for_appointment],
            ['key' => 'document_check_completed', 'label' => 'Revisión de documentos completada', 'completed' => (bool) $visa->document_check_completed],
            ['key' => 'interview_result', 'label' => 'Resultado de entrevista', 'completed' => $visa->interview_result && $visa->interview_result !== 'pending', 'meta' => $resultLabel],
            ['key' => 'pre_departure_orientation_completed', 'label' => 'Orientación pre-partida', 'completed' => (bool) $visa->pre_departure_orientation_completed],
            ['key' => 'departure', 'label' => 'Partida hacia USA', 'completed' => (bool) $visa->departure_datetime, 'meta' => $visa->departure_datetime?->toIso8601String()],
        ];
    }
}
