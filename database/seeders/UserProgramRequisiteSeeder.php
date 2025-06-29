<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\ProgramRequisite;
use App\Models\UserProgramRequisite;
use Illuminate\Database\Seeder;

class UserProgramRequisiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $applications = Application::all();
        
        foreach ($applications as $application) {
            // Obtener todos los requisitos para el programa de esta aplicación
            $programRequisites = ProgramRequisite::where('program_id', $application->program_id)->get();
            
            foreach ($programRequisites as $requisite) {
                // Determinar el estado basado en el estado de la aplicación
                $status = $this->determineRequisiteStatus($application->status, $requisite->is_required);
                
                // Crear fechas apropiadas según el estado
                $completedAt = null;
                $verifiedAt = null;
                $filePath = null;
                $observations = null;
                
                if (in_array($status, ['completed', 'verified', 'rejected'])) {
                    $completedAt = $application->applied_at->addDays(rand(1, 5));
                    
                    if ($requisite->type === 'document') {
                        $filePath = 'uploads/documents/user_' . $application->user_id . '_req_' . $requisite->id . '.pdf';
                    }
                }
                
                if (in_array($status, ['verified', 'rejected'])) {
                    $verifiedAt = $completedAt->addDays(rand(1, 3));
                    
                    if ($status === 'rejected') {
                        $observations = $this->getRandomObservation($requisite->type);
                    }
                }
                
                // Crear el registro de requisito de usuario
                UserProgramRequisite::create([
                    'application_id' => $application->id,
                    'program_requisite_id' => $requisite->id,
                    'status' => $status,
                    'file_path' => $filePath,
                    'observations' => $observations,
                    'completed_at' => $completedAt,
                    'verified_at' => $verifiedAt,
                    'created_at' => $application->applied_at,
                    'updated_at' => $verifiedAt ?? $completedAt ?? $application->applied_at,
                ]);
            }
        }
    }
    
    /**
     * Determina el estado del requisito basado en el estado de la aplicación
     */
    private function determineRequisiteStatus(string $applicationStatus, bool $isRequired): string
    {
        if ($applicationStatus === 'pending') {
            // Si la aplicación está pendiente, algunos requisitos pueden estar pendientes y otros completados
            return rand(0, 100) < 40 ? 'pending' : 'completed';
        } elseif ($applicationStatus === 'in_review') {
            // Si la aplicación está en revisión, la mayoría de los requisitos deben estar completados o verificados
            $rand = rand(0, 100);
            if ($rand < 60) {
                return 'completed';
            } elseif ($rand < 90) {
                return 'verified';
            } else {
                return 'rejected';
            }
        } elseif ($applicationStatus === 'approved') {
            // Si la aplicación está aprobada, la mayoría de los requisitos deben estar verificados
            return rand(0, 100) < 90 ? 'verified' : 'completed';
        } else { // rejected
            // Si la aplicación está rechazada, debe haber algunos requisitos rechazados
            $statuses = ['completed', 'verified', 'rejected'];
            $weights = [20, 30, 50];
            return $this->getRandomWeighted($statuses, $weights);
        }
    }
    
    /**
     * Devuelve una observación aleatoria basada en el tipo de requisito
     */
    private function getRandomObservation(string $type): string
    {
        $observations = [
            'document' => [
                'El documento está incompleto. Por favor, envíe todas las páginas.',
                'El documento no es legible. Por favor, envíe una copia de mejor calidad.',
                'El documento ha expirado. Por favor, envíe una versión vigente.',
                'El documento no está firmado. Por favor, firme y vuelva a enviar.',
                'El formato del documento no es válido. Por favor, envíe en formato PDF.',
            ],
            'payment' => [
                'El comprobante de pago no muestra la fecha de la transacción.',
                'El monto pagado no corresponde al requerido.',
                'El comprobante no muestra el nombre del beneficiario.',
                'La referencia de pago no coincide con nuestros registros.',
                'El pago está pendiente de confirmación por el banco.',
            ],
            'action' => [
                'La acción no se completó correctamente. Por favor, intente nuevamente.',
                'Falta información en el formulario completado.',
                'La acción requiere verificación adicional.',
                'No se ha completado todos los pasos requeridos.',
                'La información proporcionada es inconsistente con otros datos.',
            ],
        ];
        
        $typeObservations = $observations[$type] ?? $observations['document'];
        return $typeObservations[array_rand($typeObservations)];
    }
    
    /**
     * Devuelve un elemento aleatorio de un array basado en pesos
     */
    private function getRandomWeighted(array $items, array $weights): mixed
    {
        $totalWeight = array_sum($weights);
        $rand = mt_rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($items as $index => $item) {
            $currentWeight += $weights[$index];
            if ($rand <= $currentWeight) {
                return $item;
            }
        }
        
        return $items[0]; // Fallback
    }
}
