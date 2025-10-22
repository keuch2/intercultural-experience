<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class CommunicationController extends Controller
{
    /**
     * Display a listing of communications
     */
    public function index(Request $request)
    {
        $query = DB::table('email_logs')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('recipients', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $communications = $query->paginate(20);

        $stats = [
            'total' => DB::table('email_logs')->count(),
            'today' => DB::table('email_logs')->whereDate('created_at', today())->count(),
            'this_week' => DB::table('email_logs')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => DB::table('email_logs')->whereMonth('created_at', now()->month)->count(),
        ];

        return view('admin.communications.index', compact('communications', 'stats'));
    }

    /**
     * Show the form for creating a new communication
     */
    public function create()
    {
        $programs = Program::select('id', 'name', 'main_category')->get();
        $templates = $this->getTemplates();
        
        return view('admin.communications.create', compact('programs', 'templates'));
    }

    /**
     * Get recipients based on filters
     */
    public function getRecipients(Request $request)
    {
        $query = User::role('participant');

        if ($request->filled('program_id')) {
            $query->whereHas('applications', function($q) use ($request) {
                $q->where('program_id', $request->program_id);
            });
        }

        if ($request->filled('program_category')) {
            $query->whereHas('applications.program', function($q) use ($request) {
                $q->where('main_category', $request->program_category);
            });
        }

        if ($request->filled('application_status')) {
            $query->whereHas('applications', function($q) use ($request) {
                $q->where('status', $request->application_status);
            });
        }

        if ($request->filled('english_level')) {
            $query->whereHas('englishEvaluations', function($q) use ($request) {
                $q->where('cefr_level', $request->english_level)
                  ->whereRaw('id IN (SELECT MAX(id) FROM english_evaluations GROUP BY user_id)');
            });
        }

        $recipients = $query->select('id', 'name', 'email')->get();

        return response()->json([
            'count' => $recipients->count(),
            'recipients' => $recipients,
        ]);
    }

    /**
     * Send mass email
     */
    public function send(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'recipient_ids' => 'required|array|min:1',
            'recipient_ids.*' => 'exists:users,id',
        ]);

        $recipients = User::whereIn('id', $request->recipient_ids)->get();
        $successCount = 0;
        $failCount = 0;

        foreach ($recipients as $recipient) {
            try {
                // Reemplazar variables dinámicas
                $personalizedMessage = $this->replaceVariables($request->message, $recipient);
                $personalizedSubject = $this->replaceVariables($request->subject, $recipient);

                // Enviar email (simulado por ahora)
                // Mail::raw($personalizedMessage, function ($message) use ($recipient, $personalizedSubject) {
                //     $message->to($recipient->email)
                //             ->subject($personalizedSubject);
                // });

                $successCount++;

                // Log el envío
                DB::table('email_logs')->insert([
                    'recipient_email' => $recipient->email,
                    'recipient_name' => $recipient->name,
                    'subject' => $personalizedSubject,
                    'message' => $personalizedMessage,
                    'sent_by' => auth()->id(),
                    'status' => 'sent',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            } catch (\Exception $e) {
                $failCount++;
                
                // Log el error
                DB::table('email_logs')->insert([
                    'recipient_email' => $recipient->email,
                    'recipient_name' => $recipient->name,
                    'subject' => $personalizedSubject ?? $request->subject,
                    'message' => $personalizedMessage ?? $request->message,
                    'sent_by' => auth()->id(),
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $message = "Emails enviados: {$successCount}";
        if ($failCount > 0) {
            $message .= ", Fallidos: {$failCount}";
        }

        return redirect()->route('admin.communications.index')
            ->with('success', $message);
    }

    /**
     * Show communication templates
     */
    public function templates()
    {
        $templates = $this->getTemplates();
        
        return view('admin.communications.templates', compact('templates'));
    }

    /**
     * Show communication history
     */
    public function history(Request $request)
    {
        $query = DB::table('email_logs')
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('recipient_email', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(50);

        return view('admin.communications.history', compact('logs'));
    }

    /**
     * Replace variables in message
     */
    private function replaceVariables($text, $user)
    {
        $variables = [
            '{nombre}' => $user->name,
            '{email}' => $user->email,
            '{telefono}' => $user->phone ?? 'N/A',
            '{fecha}' => now()->format('d/m/Y'),
        ];

        // Si tiene aplicaciones, agregar datos del programa
        $latestApplication = $user->applications()->latest()->first();
        if ($latestApplication) {
            $variables['{programa}'] = $latestApplication->program->name ?? 'N/A';
            $variables['{categoria_programa}'] = $latestApplication->program->main_category ?? 'N/A';
        }

        // Si tiene evaluaciones de inglés
        $latestEvaluation = $user->englishEvaluations()->latest()->first();
        if ($latestEvaluation) {
            $variables['{nivel_ingles}'] = $latestEvaluation->cefr_level ?? 'N/A';
            $variables['{puntaje_ingles}'] = $latestEvaluation->score ?? 'N/A';
        }

        return str_replace(array_keys($variables), array_values($variables), $text);
    }

    /**
     * Get email templates
     */
    private function getTemplates()
    {
        return [
            [
                'name' => 'Bienvenida',
                'subject' => 'Bienvenido a {programa}',
                'message' => "Hola {nombre},\n\nBienvenido al programa {programa}. Estamos emocionados de tenerte con nosotros.\n\nSaludos,\nEquipo IE",
            ],
            [
                'name' => 'Recordatorio Documentos',
                'subject' => 'Recordatorio: Documentos Pendientes',
                'message' => "Hola {nombre},\n\nTe recordamos que tienes documentos pendientes de subir para el programa {programa}.\n\nPor favor, accede a tu perfil y completa la documentación.\n\nSaludos,\nEquipo IE",
            ],
            [
                'name' => 'Evaluación de Inglés',
                'subject' => 'Resultados Evaluación de Inglés',
                'message' => "Hola {nombre},\n\nTus resultados de la evaluación de inglés están disponibles.\n\nNivel alcanzado: {nivel_ingles}\nPuntaje: {puntaje_ingles}/100\n\nSaludos,\nEquipo IE",
            ],
            [
                'name' => 'Oferta Laboral',
                'subject' => 'Nueva Oferta Laboral Disponible',
                'message' => "Hola {nombre},\n\nTenemos nuevas ofertas laborales que podrían interesarte para el programa {programa}.\n\nRevisa tu panel de participante para más detalles.\n\nSaludos,\nEquipo IE",
            ],
            [
                'name' => 'Recordatorio Cita Consular',
                'subject' => 'Recordatorio: Cita Consular Próxima',
                'message' => "Hola {nombre},\n\nTe recordamos tu cita consular próxima. Asegúrate de tener todos los documentos necesarios.\n\nFecha: {fecha}\n\nSaludos,\nEquipo IE",
            ],
        ];
    }
}
