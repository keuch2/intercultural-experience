<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * La app pasó de UTC a America/Asuncion (UTC-3 fijo desde oct 2024). Las fechas que generó el
 * sistema (now()) quedaron guardadas en UTC → se les restan 3 horas para que se lean en hora de
 * Paraguay.
 *
 * NO se tocan:
 *  - fechas cargadas a mano por una persona (citas, entrevistas, vuelos, inscripciones): ya están
 *    en hora local;
 *  - valores que puso MySQL por defecto (CURRENT_TIMESTAMP): el servidor ya corre en UTC-3.
 *    Es el caso de activity_logs con log_name = 'admin_activity' (middleware ActivityLogger) y
 *    de points.created_at.
 */
return new class extends Migration
{
    private const HOURS = 3;

    /** Columnas cargadas por personas (hora local). */
    private const USER_ENTERED = [
        'au_pair_visa_processes' => ['arrival_usa_datetime', 'departure_datetime'],
        'program_visa_processes' => ['arrival_usa_datetime', 'departure_datetime'],
        'job_fair_events' => ['registration_opens', 'registration_closes'],
        'job_fair_registrations' => ['payment_date'],
        'visa_processes' => ['consular_appointment_date', 'job_interview_date', 'sponsor_interview_date'],
        'work_travel_data' => ['job_interview_date', 'sponsor_interview_date'],
        'work_contracts' => ['signed_at', 'verification_date'],
    ];

    /** Columnas completadas por el DEFAULT de MySQL (ya en hora local). */
    private const DB_DEFAULT = [
        'points' => ['created_at'],
    ];

    /** Filas a excluir por tabla (también generadas por el DEFAULT de MySQL). */
    private const ROW_FILTERS = [
        'activity_logs' => "log_name IS NULL OR log_name <> 'admin_activity'",
    ];

    public function up(): void
    {
        $this->shift('-');
    }

    public function down(): void
    {
        $this->shift('+');
    }

    private function shift(string $sign): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $columns = DB::table('information_schema.columns')
            ->where('table_schema', DB::getDatabaseName())
            ->whereIn('data_type', ['timestamp', 'datetime'])
            ->orderBy('table_name')
            ->get(['table_name', 'column_name'])
            ->map(fn ($c) => ['table' => $c->table_name ?? $c->TABLE_NAME, 'column' => $c->column_name ?? $c->COLUMN_NAME])
            ->reject(fn ($c) => in_array($c['column'], self::USER_ENTERED[$c['table']] ?? [], true)
                || in_array($c['column'], self::DB_DEFAULT[$c['table']] ?? [], true))
            ->groupBy('table');

        foreach ($columns as $table => $cols) {
            // Una sola sentencia por tabla; los valores vacíos o "cero" quedan como están.
            $sets = $cols->map(fn ($c) => sprintf(
                "`%1\$s` = IF(`%1\$s` > '1970-01-02', `%1\$s` %2\$s INTERVAL %3\$d HOUR, `%1\$s`)",
                $c['column'], $sign, self::HOURS
            ))->implode(', ');
            $where = isset(self::ROW_FILTERS[$table]) ? ' WHERE '.self::ROW_FILTERS[$table] : '';

            DB::statement("UPDATE `{$table}` SET {$sets}{$where}");
        }
    }
};
