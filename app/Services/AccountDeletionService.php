<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\AuPairProcess;
use App\Models\ProgramProcess;
use App\Models\User;
use App\Services\ProgramEngine\JobPoolService;
use App\Services\ProgramEngine\Notifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Eliminación de cuenta pedida por el participante desde la app (requisito App Store 5.1.1(v)).
 *
 * Anonimiza en lugar de borrar: elimina datos personales, fotos y documentos, cancela los procesos
 * activos y revoca las sesiones; las postulaciones y pagos quedan (sin datos personales) para la
 * contabilidad de IE. Los comprobantes de pago se conservan por obligación contable.
 */
class AccountDeletionService
{
    public const DISK = 'public';

    public const DELETED_NAME = 'Usuario eliminado';

    /** Columnas de users que se conservan (identidad técnica, rol y auditoría). */
    private const KEEP_USER_COLUMNS = [
        'id', 'name', 'email', 'password', 'role', 'role_id', 'created_by_agent_id',
        'requires_password_setup', 'anonymized_at', 'created_at', 'updated_at',
    ];

    /** Tablas por user_id con datos personales: se borran las filas (y sus archivos). */
    private const USER_ROWS = [
        'emergency_contacts' => [],
        'work_experiences' => [],
        'work_experiences_detailed' => [],
        'health_declarations' => [],
        'childcare_experiences' => [],
        'references' => ['letter_file_path'],
        'teacher_certifications' => ['document_path'],
        'au_pair_profiles' => ['main_photo', 'photos'],
        'password_history' => [],
        'notifications' => [],
        'participant_reminders' => [],
        'sessions' => [],
        'form_submissions' => [],
    ];

    /** Tablas por user_id que se conservan: solo se borran los archivos. */
    private const USER_FILES = [
        'teacher_validations' => ['child_abuse_clearance_path', 'criminal_record_path', 'diploma_path', 'mec_certificate_path', 'tefl_certificate_path', 'tesol_certificate_path', 'transcript_path'],
        'visa_processes' => ['consular_fee_receipt_path', 'ds160_file_path', 'ds2019_file_path', 'passport_file_path', 'sevis_receipt_path', 'visa_photo_path'],
        'work_travel_validations' => ['enrollment_certificate_path'],
        'language_programs' => ['acceptance_letter_path', 'certificate_path', 'visa_support_letter_path'],
        'work_study_programs' => ['acceptance_letter_path', 'i20_document_path'],
        'work_study_placements' => ['contract_signed_path', 'job_offer_letter_path', 'performance_review_path', 'work_permit_path'],
        'scholarship_applications' => ['additional_documents_paths', 'essay_path', 'financial_need_statement_path', 'motivation_letter_path', 'portfolio_path'],
    ];

    /** Tablas por application_id: [columnas de archivo, borrar filas]. */
    private const APPLICATION_TABLES = [
        'application_documents' => [['file_path'], true],
        'au_pair_data' => [['cpr_cert_path', 'dear_host_family_letter_path', 'drivers_license_path', 'first_aid_cert_path', 'presentation_video_path'], true],
        'teacher_data' => [['academic_transcript_path', 'degree_certificate_path', 'mec_certificate_path'], true],
        'work_travel_data' => [['university_certificate_path'], true],
        'user_program_requisites' => [['file_path'], false],
    ];

    /** Tablas por program_process_id (motor): [columnas de archivo, borrar filas]. */
    private const ENGINE_TABLES = [
        'program_documents' => [['file_path'], true],
        'program_process_checklist' => [['file_path'], false],
        'program_english_tests' => [['test_pdf_path'], false],
        'program_visa_processes' => [['appointment_instructions_path', 'ds160_path', 'ds2019_path', 'participation_letter_path', 'visa_form_path', 'visa_photo_path'], false],
    ];

    /** Tablas por au_pair_process_id: [columnas de archivo, borrar filas]. */
    private const AU_PAIR_TABLES = [
        'au_pair_documents' => [['file_path'], true],
        'au_pair_english_tests' => [['test_pdf_path'], false],
        'au_pair_visa_processes' => [['appointment_instructions_path', 'ds160_path', 'ds2019_path', 'participation_letter_path', 'visa_form_path', 'visa_photo_path'], false],
    ];

    /** @var array<string, array<string, bool>> nullabilidad por tabla/columna */
    private array $nullable = [];

    /** @var list<string> archivos a borrar cuando la transacción confirma */
    private array $files = [];

    public function __construct(
        private readonly JobPoolService $jobPool,
        private readonly Notifier $notifier,
    ) {}

    public function anonymize(User $user): void
    {
        $this->files = [];
        $userId = $user->id;

        DB::transaction(function () use ($user, $userId) {
            $applicationIds = DB::table('applications')->where('user_id', $userId)->pluck('id')->all();

            $this->closeEngineProcesses($user);
            $this->closeAuPairProcesses($userId);

            foreach (self::USER_ROWS as $table => $cols) {
                $this->purge($table, 'user_id', [$userId], $cols, true);
            }
            foreach (self::USER_FILES as $table => $cols) {
                $this->purge($table, 'user_id', [$userId], $cols, false);
            }
            foreach (self::APPLICATION_TABLES as $table => [$cols, $deleteRows]) {
                $this->purge($table, 'application_id', $applicationIds, $cols, $deleteRows);
            }

            $this->anonymizeUserRow($user);
            $user->tokens()->delete();

            ActivityLog::log('participants')->withAction('account_deleted')
                ->withProperties(['user_id' => $userId, 'applications' => count($applicationIds)])
                ->log("Cuenta #{$userId} eliminada por el participante desde la app");
        });

        $disk = Storage::disk(self::DISK);
        foreach (array_unique($this->files) as $path) {
            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        }

        $this->notifier->toAdmins(
            'Cuenta eliminada por un participante',
            "El participante #{$userId} eliminó su cuenta desde la app. Sus datos personales y documentos fueron borrados; postulaciones y pagos quedaron anonimizados.",
            'participants',
        );
    }

    private function closeEngineProcesses(User $user): void
    {
        $processes = ProgramProcess::withTrashed()->where('user_id', $user->id)->with('program')->get();

        foreach ($processes as $process) {
            if ($assignment = $process->activeJobAssignment()->first()) {
                $this->jobPool->release($assignment, null, 'Cuenta eliminada por el participante');
            }
            if ($process->status === ProgramProcess::STATUS_ACTIVE) {
                $process->forceFill(['status' => ProgramProcess::STATUS_CANCELLED])->save();
            }

            $slug = $process->program?->slug ?? $process->program_id;
            foreach (Storage::disk(self::DISK)->allFiles("program-docs/{$slug}/{$process->id}") as $path) {
                $this->files[] = $path;
            }
        }

        foreach (self::ENGINE_TABLES as $table => [$cols, $deleteRows]) {
            $this->purge($table, 'program_process_id', $processes->pluck('id')->all(), $cols, $deleteRows);
        }
    }

    private function closeAuPairProcesses(int $userId): void
    {
        $ids = AuPairProcess::where('user_id', $userId)->pluck('id')->all();

        AuPairProcess::whereIn('id', $ids)->whereNotIn('current_stage', ['completed', 'cancelled'])
            ->update(['current_stage' => 'cancelled']);

        foreach (self::AU_PAIR_TABLES as $table => [$cols, $deleteRows]) {
            $this->purge($table, 'au_pair_process_id', $ids, $cols, $deleteRows);
        }
        $this->purge('au_pair_processes', 'id', $ids, ['contract_file_path'], false);
    }

    /**
     * Junta los archivos de $fileColumns de las filas indicadas y luego borra las filas o
     * deja esas columnas en null. Ignora tablas/columnas que no existan en este esquema.
     */
    private function purge(string $table, string $key, array $ids, array $fileColumns, bool $deleteRows): void
    {
        if ($ids === [] || ! Schema::hasTable($table) || ! Schema::hasColumn($table, $key)) {
            return;
        }

        $fileColumns = array_values(array_filter($fileColumns, fn ($c) => Schema::hasColumn($table, $c)));
        $query = fn () => DB::table($table)->whereIn($key, $ids);

        if ($fileColumns !== []) {
            foreach ($query()->get($fileColumns) as $row) {
                foreach ($fileColumns as $col) {
                    $this->collectPaths($row->{$col});
                }
            }
        }

        if ($deleteRows) {
            $query()->delete();

            return;
        }

        $nullable = array_filter($fileColumns, fn ($c) => $this->isNullable($table, $c));
        if ($nullable !== []) {
            $query()->update(array_fill_keys($nullable, null));
        }
    }

    private function collectPaths(mixed $value): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }
        $decoded = str_starts_with(ltrim($value), '[') ? json_decode($value, true) : null;
        foreach (is_array($decoded) ? $decoded : [$value] as $path) {
            if (is_string($path) && $path !== '' && ! str_starts_with($path, 'http')) {
                $this->files[] = $path;
            }
        }
    }

    private function anonymizeUserRow(User $user): void
    {
        foreach (['profile_photo', 'avatar'] as $col) {
            $this->collectPaths($user->getRawOriginal($col));
        }

        $data = [];
        foreach (Schema::getColumns('users') as $column) {
            if ($column['nullable'] && ! in_array($column['name'], self::KEEP_USER_COLUMNS, true)) {
                $data[$column['name']] = null;
            }
        }

        // Query builder para no pasar por mutators (bank_info) ni el log de actividad del modelo.
        DB::table('users')->where('id', $user->id)->update($data + [
            'name' => self::DELETED_NAME,
            'email' => "eliminado-{$user->id}-".Str::lower(Str::random(8)).'@ie.invalid',
            'password' => Hash::make(Str::random(64)),
            'anonymized_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function isNullable(string $table, string $column): bool
    {
        $this->nullable[$table] ??= collect(Schema::getColumns($table))->pluck('nullable', 'name')->all();

        return (bool) ($this->nullable[$table][$column] ?? false);
    }
}
