<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Controlador para importación masiva de datos
 * Épica 3 - Sprint 3
 */
class AdminBulkImportController extends Controller
{
    /**
     * Muestra el módulo de importación masiva
     */
    public function index()
    {
        return view('admin.bulk-import.index');
    }

    /**
     * Descarga la plantilla de ejemplo para usuarios
     */
    public function downloadTemplate($type = 'users')
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Configurar headers según el tipo
        if ($type === 'users' || $type === 'participants') {
            $sheet->setCellValue('A1', 'Nombre Completo *');
            $sheet->setCellValue('B1', 'Email *');
            $sheet->setCellValue('C1', 'Teléfono *');
            $sheet->setCellValue('D1', 'País *');
            $sheet->setCellValue('E1', 'Ciudad');
            $sheet->setCellValue('F1', 'Nacionalidad *');
            $sheet->setCellValue('G1', 'Fecha de Nacimiento (YYYY-MM-DD) *');
            $sheet->setCellValue('H1', 'Dirección');
            $sheet->setCellValue('I1', 'Nivel Académico');
            $sheet->setCellValue('J1', 'Nivel de Inglés');
            
            // Fila de ejemplo
            $sheet->setCellValue('A2', 'Juan Pérez');
            $sheet->setCellValue('B2', 'juan.perez@example.com');
            $sheet->setCellValue('C2', '+595 981 123456');
            $sheet->setCellValue('D2', 'Paraguay');
            $sheet->setCellValue('E2', 'Asunción');
            $sheet->setCellValue('F2', 'Paraguayo');
            $sheet->setCellValue('G2', '2000-05-15');
            $sheet->setCellValue('H2', 'Av. España 123');
            $sheet->setCellValue('I2', 'Universitario');
            $sheet->setCellValue('J2', 'Intermedio');
            
            // Estilo para headers
            $sheet->getStyle('A1:J1')->getFont()->setBold(true);
            $sheet->getStyle('A1:J1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF4472C4');
            $sheet->getStyle('A1:J1')->getFont()->getColor()->setARGB('FFFFFFFF');
            
            // Ajustar ancho de columnas
            foreach(range('A','J') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        } elseif ($type === 'agents') {
            $sheet->setCellValue('A1', 'Nombre Completo *');
            $sheet->setCellValue('B1', 'Email *');
            $sheet->setCellValue('C1', 'Teléfono *');
            $sheet->setCellValue('D1', 'País *');
            $sheet->setCellValue('E1', 'Ciudad');
            $sheet->setCellValue('F1', 'Nacionalidad');
            
            // Fila de ejemplo
            $sheet->setCellValue('A2', 'María González');
            $sheet->setCellValue('B2', 'maria.gonzalez@example.com');
            $sheet->setCellValue('C2', '+595 982 654321');
            $sheet->setCellValue('D2', 'Paraguay');
            $sheet->setCellValue('E2', 'Asunción');
            $sheet->setCellValue('F2', 'Paraguaya');
            
            $sheet->getStyle('A1:F1')->getFont()->setBold(true);
            $sheet->getStyle('A1:F1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF70AD47');
            $sheet->getStyle('A1:F1')->getFont()->getColor()->setARGB('FFFFFFFF');
            
            foreach(range('A','F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'plantilla_' . $type . '_' . date('Y-m-d') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Preview de datos del archivo subido
     */
    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
            'type' => 'required|in:users,participants,agents',
        ]);

        $file = $request->file('file');
        $type = $request->type;
        
        // Cargar el archivo
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();
        
        // La primera fila son los headers
        $headers = array_shift($data);
        
        // Validar y procesar cada fila
        $preview = [];
        $errors = [];
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($data as $index => $row) {
            $rowNumber = $index + 2; // +2 porque index empieza en 0 y hay 1 fila de header
            
            // Saltar filas vacías
            if (empty(array_filter($row))) {
                continue;
            }
            
            $rowData = [
                'row_number' => $rowNumber,
                'name' => $row[0] ?? '',
                'email' => $row[1] ?? '',
                'phone' => $row[2] ?? '',
                'country' => $row[3] ?? '',
                'city' => $row[4] ?? '',
                'nationality' => $row[5] ?? '',
                'birth_date' => isset($row[6]) ? $row[6] : null,
                'address' => $row[7] ?? '',
                'academic_level' => $row[8] ?? '',
                'english_level' => $row[9] ?? '',
                'errors' => [],
                'valid' => true
            ];
            
            // Validar datos
            $validator = Validator::make($rowData, [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required|string|max:50',
                'country' => 'required|string|max:100',
                'nationality' => 'required|string|max:100',
                'birth_date' => 'required|date',
            ]);
            
            if ($validator->fails()) {
                $rowData['errors'] = $validator->errors()->all();
                $rowData['valid'] = false;
                $errorCount++;
            } else {
                $successCount++;
            }
            
            $preview[] = $rowData;
        }
        
        return view('admin.bulk-import.preview', compact('preview', 'type', 'successCount', 'errorCount'));
    }

    /**
     * Procesa la importación de datos
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'type' => 'required|in:users,participants,agents',
            'send_emails' => 'boolean',
        ]);

        $file = $request->file('file');
        $type = $request->type;
        $sendEmails = $request->boolean('send_emails', false);
        
        // Cargar el archivo
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();
        
        // Remover headers
        array_shift($data);
        
        $imported = [];
        $failed = [];
        $passwords = [];
        
        foreach ($data as $index => $row) {
            $rowNumber = $index + 2;
            
            // Saltar filas vacías
            if (empty(array_filter($row))) {
                continue;
            }
            
            try {
                // Validar datos básicos
                $validator = Validator::make([
                    'name' => $row[0] ?? '',
                    'email' => $row[1] ?? '',
                    'phone' => $row[2] ?? '',
                    'country' => $row[3] ?? '',
                    'nationality' => $row[5] ?? '',
                    'birth_date' => $row[6] ?? '',
                ], [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email',
                    'phone' => 'required|string|max:50',
                    'country' => 'required|string|max:100',
                    'nationality' => 'required|string|max:100',
                    'birth_date' => 'required|date',
                ]);
                
                if ($validator->fails()) {
                    $failed[] = [
                        'row' => $rowNumber,
                        'data' => $row,
                        'errors' => $validator->errors()->all()
                    ];
                    continue;
                }
                
                // Generar contraseña temporal
                $temporaryPassword = Str::random(12);
                
                // Crear usuario
                $user = User::create([
                    'name' => $row[0],
                    'email' => $row[1],
                    'password' => Hash::make($temporaryPassword),
                    'role' => $type === 'agents' ? 'agent' : 'user',
                    'phone' => $row[2],
                    'country' => $row[3],
                    'city' => $row[4] ?? null,
                    'nationality' => $row[5],
                    'birth_date' => $row[6],
                    'address' => $row[7] ?? null,
                    'academic_level' => $row[8] ?? null,
                    'english_level' => $row[9] ?? null,
                    'email_verified_at' => now(),
                ]);
                
                $imported[] = $user;
                $passwords[$user->email] = $temporaryPassword;
                
                // Enviar email si está habilitado
                if ($sendEmails) {
                    // TODO: Enviar email con credenciales (se implementará con Épica 2 completada)
                    // Mail::to($user->email)->send(new CredentialsSent($user, $temporaryPassword));
                }
                
            } catch (\Exception $e) {
                $failed[] = [
                    'row' => $rowNumber,
                    'data' => $row,
                    'errors' => [$e->getMessage()]
                ];
            }
        }
        
        // Generar reporte
        $reportPath = $this->generateReport($imported, $failed, $passwords, $type);
        
        return view('admin.bulk-import.result', compact('imported', 'failed', 'passwords', 'type', 'reportPath'));
    }

    /**
     * Genera reporte de importación
     */
    private function generateReport($imported, $failed, $passwords, $type)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Reporte de éxitos
        $sheet->setTitle('Importados');
        $sheet->setCellValue('A1', 'Nombre');
        $sheet->setCellValue('B1', 'Email');
        $sheet->setCellValue('C1', 'Contraseña Temporal');
        $sheet->setCellValue('D1', 'Rol');
        $sheet->setCellValue('E1', 'Fecha de Creación');
        
        $row = 2;
        foreach ($imported as $user) {
            $sheet->setCellValue('A' . $row, $user->name);
            $sheet->setCellValue('B' . $row, $user->email);
            $sheet->setCellValue('C' . $row, $passwords[$user->email] ?? 'N/A');
            $sheet->setCellValue('D' . $row, ucfirst($user->role));
            $sheet->setCellValue('E' . $row, $user->created_at->format('d/m/Y H:i'));
            $row++;
        }
        
        // Hoja de errores si hay
        if (!empty($failed)) {
            $sheet2 = $spreadsheet->createSheet();
            $sheet2->setTitle('Errores');
            $sheet2->setCellValue('A1', 'Fila');
            $sheet2->setCellValue('B1', 'Datos');
            $sheet2->setCellValue('C1', 'Errores');
            
            $row = 2;
            foreach ($failed as $error) {
                $sheet2->setCellValue('A' . $row, $error['row']);
                $sheet2->setCellValue('B' . $row, implode(' | ', $error['data']));
                $sheet2->setCellValue('C' . $row, implode(', ', $error['errors']));
                $row++;
            }
        }
        
        // Guardar archivo
        $filename = 'reporte_importacion_' . $type . '_' . date('Ymd_His') . '.xlsx';
        $path = 'reports/' . $filename;
        
        $writer = new Xlsx($spreadsheet);
        $writer->save(storage_path('app/public/' . $path));
        
        return $path;
    }
}
