<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Program;
use App\Models\ProgramRequisite;
use App\Models\Application;
use App\Models\UserProgramRequisite;

class SyncProgramRequisites extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'requisites:sync {--program= : ID del programa específico} {--dry-run : Mostrar qué se haría sin ejecutar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza los requisitos de programas con las aplicaciones existentes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $programId = $this->option('program');
        
        $this->info('Iniciando sincronización de requisitos...');
        if ($dryRun) {
            $this->warn('Modo DRY-RUN: No se realizarán cambios');
        }
        
        // Obtener programas a procesar
        $programs = $programId 
            ? Program::where('id', $programId)->get()
            : Program::all();
        
        if ($programs->isEmpty()) {
            $this->error('No se encontraron programas');
            return 1;
        }
        
        $totalCreated = 0;
        $totalSkipped = 0;
        
        foreach ($programs as $program) {
            $this->line("\nProcesando programa: {$program->name} (ID: {$program->id})");
            
            // Obtener todos los requisitos del programa
            $requisites = ProgramRequisite::where('program_id', $program->id)->get();
            
            if ($requisites->isEmpty()) {
                $this->comment("  No tiene requisitos");
                continue;
            }
            
            // Obtener todas las aplicaciones del programa
            $applications = Application::where('program_id', $program->id)->get();
            
            if ($applications->isEmpty()) {
                $this->comment("  No tiene aplicaciones");
                continue;
            }
            
            $this->info("  Requisitos: {$requisites->count()} | Aplicaciones: {$applications->count()}");
            
            // Para cada requisito, verificar que exista en cada aplicación
            foreach ($requisites as $requisite) {
                $this->line("  Requisito: {$requisite->name} (Tipo: {$requisite->type})");
                
                foreach ($applications as $application) {
                    // Verificar si ya existe
                    $exists = UserProgramRequisite::where('application_id', $application->id)
                        ->where('program_requisite_id', $requisite->id)
                        ->exists();
                    
                    if ($exists) {
                        $totalSkipped++;
                        continue;
                    }
                    
                    // Crear el UserProgramRequisite faltante
                    if (!$dryRun) {
                        try {
                            UserProgramRequisite::create([
                                'application_id' => $application->id,
                                'program_requisite_id' => $requisite->id,
                                'status' => 'pending'
                            ]);
                            $this->info("    ✓ Creado para aplicación #{$application->id} (Usuario: {$application->user->name})");
                            $totalCreated++;
                        } catch (\Exception $e) {
                            $this->error("    ✗ Error para aplicación #{$application->id}: {$e->getMessage()}");
                        }
                    } else {
                        $this->comment("    → Se crearía para aplicación #{$application->id} (Usuario: {$application->user->name})");
                        $totalCreated++;
                    }
                }
            }
        }
        
        $this->newLine();
        $this->info("Resumen:");
        $this->line("  Creados: {$totalCreated}");
        $this->line("  Ya existían: {$totalSkipped}");
        
        if ($dryRun) {
            $this->warn("\nEjecuta sin --dry-run para aplicar los cambios");
        } else {
            $this->success("\n✓ Sincronización completada");
        }
        
        return 0;
    }
}
