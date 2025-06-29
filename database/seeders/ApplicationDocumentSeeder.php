<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\ApplicationDocument;
use Illuminate\Database\Seeder;

class ApplicationDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $applications = Application::all();
        $documentNames = [
            'Pasaporte', 
            'Curriculum Vitae', 
            'Carta de Motivación', 
            'Certificado de Idioma', 
            'Historial Académico', 
            'Fotografía', 
            'Visa', 
            'Seguro Médico', 
            'Carta de Recomendación'
        ];
        $statuses = ['pending', 'uploaded', 'verified', 'rejected'];
        
        foreach ($applications as $application) {
            // Cada aplicación tendrá entre 3 y 6 documentos
            $numDocuments = rand(3, 6);
            $usedNames = [];
            
            for ($i = 0; $i < $numDocuments; $i++) {
                // Seleccionar un nombre de documento que no se haya usado para esta aplicación
                do {
                    $name = $documentNames[array_rand($documentNames)];
                } while (in_array($name, $usedNames));
                
                $usedNames[] = $name;
                
                // Determinar el estado del documento basado en el estado de la aplicación
                if ($application->status === 'pending') {
                    $status = rand(0, 100) < 70 ? 'pending' : 'uploaded';
                } elseif ($application->status === 'in_review') {
                    $status = $statuses[array_rand($statuses)];
                } elseif ($application->status === 'approved') {
                    $status = rand(0, 100) < 80 ? 'verified' : 'uploaded';
                } else { // rejected
                    $status = rand(0, 100) < 50 ? 'rejected' : (rand(0, 100) < 70 ? 'verified' : 'pending');
                }
                
                $uploadedAt = $application->applied_at->addDays(rand(0, 5));
                $verifiedAt = null;
                
                if (in_array($status, ['verified', 'rejected'])) {
                    $verifiedAt = (clone $uploadedAt)->addDays(rand(1, 7));
                }
                
                $observations = null;
                if ($status === 'rejected') {
                    $observations = $this->getRandomObservation($name);
                }
                
                ApplicationDocument::create([
                    'application_id' => $application->id,
                    'name' => $name,
                    'file_path' => 'uploads/documents/app_' . $application->id . '_' . str_replace(' ', '_', strtolower($name)) . '.pdf',
                    'status' => $status,
                    'observations' => $observations,
                    'uploaded_at' => $status !== 'pending' ? $uploadedAt : null,
                    'verified_at' => $verifiedAt,
                ]);
            }
        }
    }
    
    /**
     * Devuelve una observación aleatoria basada en el nombre del documento
     */
    private function getRandomObservation(string $name): string
    {
        $observations = [
            'Pasaporte' => [
                'El pasaporte está expirado. Por favor, envíe uno vigente.',
                'La imagen del pasaporte no es legible. Por favor, envíe una copia de mejor calidad.',
                'Falta la página con los datos biométricos. Por favor, envíe el pasaporte completo.',
            ],
            'Curriculum Vitae' => [
                'El CV no incluye su experiencia educativa completa. Por favor, actualícelo.',
                'El formato del CV no es el requerido. Por favor, use nuestra plantilla.',
                'El CV no está actualizado. Por favor, incluya su experiencia reciente.',
            ],
            'Carta de Motivación' => [
                'La carta de motivación es demasiado breve. Por favor, desarrolle más sus motivos.',
                'La carta no explica claramente por qué eligió este programa. Por favor, sea más específico.',
                'La carta contiene errores gramaticales. Por favor, revísela y corrija los errores.',
            ],
            'Certificado de Idioma' => [
                'El certificado de idioma ha expirado. Por favor, envíe uno reciente.',
                'El nivel de idioma no cumple con los requisitos mínimos del programa.',
                'El certificado no es de una institución reconocida. Por favor, envíe un certificado oficial.',
            ],
            'Historial Académico' => [
                'El historial académico está incompleto. Por favor, envíe todas las páginas.',
                'El historial académico no está traducido. Por favor, envíe una traducción oficial.',
                'El documento no muestra claramente sus calificaciones. Por favor, envíe un documento oficial.',
            ],
            'Fotografía' => [
                'La foto no cumple con los requisitos de tamaño. Por favor, envíe una foto 4x4 cm.',
                'La foto tiene más de 6 meses de antigüedad. Por favor, envíe una reciente.',
                'El fondo de la foto no es blanco. Por favor, envíe una foto con fondo blanco.',
            ],
            'Visa' => [
                'La visa no es del tipo correcto para este programa. Por favor, solicite la visa adecuada.',
                'La visa está a punto de expirar. Por favor, renuévela antes de iniciar el programa.',
                'El documento enviado es una solicitud, no la visa aprobada. Por favor, envíe la visa final.',
            ],
            'Seguro Médico' => [
                'El seguro médico no cubre el período completo del programa. Por favor, amplíe la cobertura.',
                'El seguro no incluye repatriación. Por favor, contrate un seguro con esta cobertura.',
                'El documento no muestra claramente la cobertura. Por favor, envíe la póliza completa.',
            ],
            'Carta de Recomendación' => [
                'La carta no está firmada. Por favor, envíe una carta con firma original.',
                'La carta no incluye información de contacto del recomendante. Por favor, inclúyala.',
                'La carta es demasiado general. Por favor, solicite una carta más específica sobre sus habilidades.',
            ],
        ];
        
        $nameObservations = $observations[$name] ?? [
            'El documento no cumple con los requisitos. Por favor, revise las instrucciones.',
            'La calidad del documento es insuficiente. Por favor, envíe una copia de mejor calidad.',
            'El documento está incompleto. Por favor, envíe todas las páginas requeridas.',
        ];
        
        return $nameObservations[array_rand($nameObservations)];
    }
}
