<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\User;
use App\Services\Exceptions\ApplicationDeletionException;
use App\Services\ProgramEngine\JobPoolService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Borrado definitivo de una postulación (admin): elimina el proceso del motor o Au Pair
 * con sus archivos, libera la oferta laboral si la tenía y deja al participante libre
 * para volver a postular al programa. Los pagos se borran en cascada, por eso si hay
 * pagos verificados se exige confirmación explícita.
 */
class ApplicationDeletionService
{
    public const DISK = 'public';

    public function __construct(private readonly JobPoolService $jobPool) {}

    /** Conteos para el modal de confirmación. */
    public function summary(Application $application): array
    {
        $engine = $application->programProcess()->withTrashed()->first();
        $auPair = $application->auPairProcess()->first();

        return [
            'documents' => ($engine?->documents()->count() ?? 0) + ($auPair?->documents()->count() ?? 0) + $application->documents()->count(),
            'payments_verified' => $application->payments()->where('status', 'verified')->count(),
            'payments_pending' => $application->payments()->where('status', '!=', 'verified')->count(),
            'has_active_assignment' => (bool) $engine?->activeJobAssignment()->exists(),
        ];
    }

    public function delete(Application $application, ?User $actor, bool $confirmPayments = false): void
    {
        $summary = $this->summary($application);
        if ($summary['payments_verified'] > 0 && ! $confirmPayments) {
            throw new ApplicationDeletionException('verified_payments', 'La postulación tiene pagos verificados. Confirmá que querés eliminarlos junto con la postulación.');
        }

        DB::transaction(function () use ($application, $actor, $summary) {
            $user = $application->user;
            $program = $application->program;

            $log = ActivityLog::log('participants')->withAction('application_deleted')
                ->withProperties(['application_id' => $application->id, 'program' => $program?->name, 'status' => $application->status] + $summary);
            if ($user) {
                $log->performedOn($user);
            }
            if ($actor) {
                $log->causedBy($actor);
            }
            $log->log("Postulación eliminada: {$user?->name} — {$program?->name}");

            $this->deleteEngineProcess($application, $actor);
            $this->deleteAuPairProcess($application);

            $wasCurrent = (bool) $application->is_current_program;
            $userId = $application->user_id;
            $application->delete();

            if ($wasCurrent) {
                Application::where('user_id', $userId)->latest('applied_at')->first()?->update(['is_current_program' => true]);
            }
        });
    }

    private function deleteEngineProcess(Application $application, ?User $actor): void
    {
        $process = $application->programProcess()->withTrashed()->first();
        if (! $process) {
            return;
        }

        $assignment = $process->activeJobAssignment()->first();
        if ($assignment) {
            $this->jobPool->release($assignment, $actor, 'Postulación eliminada');
        }

        $disk = Storage::disk(self::DISK);
        $slug = $process->program?->slug ?? $process->program_id;
        $disk->deleteDirectory("program-docs/{$slug}/{$process->id}");
        $paths = collect()
            ->merge($process->checklist()->pluck('file_path'))
            ->merge($process->englishTests()->pluck('test_pdf_path'))
            ->push($process->visaProcess?->visa_form_path, $process->visaProcess?->visa_photo_path)
            ->filter();
        foreach ($paths as $path) {
            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        }

        // forceDelete: un soft delete dejaría active_process_id ocupado en job_pool_assignments.
        $process->forceDelete();
    }

    private function deleteAuPairProcess(Application $application): void
    {
        $process = $application->auPairProcess()->first();
        if (! $process) {
            return;
        }

        $disk = Storage::disk(self::DISK);
        $paths = $process->documents()->pluck('file_path')->push($process->contract_file_path)->filter();
        foreach ($paths as $path) {
            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        }

        method_exists($process, 'forceDelete') ? $process->forceDelete() : $process->delete();
    }
}
