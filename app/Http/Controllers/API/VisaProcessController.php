<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\VisaProcess;
use App\Models\VisaStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VisaProcessController extends Controller
{
    /**
     * Get visa process by application
     */
    public function byApplication(Request $request, $applicationId)
    {
        $user = $request->user();
        
        $application = Application::where('id', $applicationId)
            ->where('user_id', $user->id)
            ->first();

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Aplicación no encontrada',
            ], 404);
        }

        $visaProcess = VisaProcess::with('statusHistory')
            ->where('application_id', $applicationId)
            ->first();

        if (!$visaProcess) {
            return response()->json([
                'success' => false,
                'message' => 'Proceso de visa no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'visa_process' => $visaProcess,
                'progress_percentage' => $visaProcess->getProgressPercentage(),
                'next_step' => $visaProcess->getNextStep(),
                'can_advance' => $visaProcess->canAdvanceToNextStatus(),
                'timeline' => $visaProcess->getTimeline(),
            ],
        ]);
    }

    /**
     * Get visa process timeline
     */
    public function timeline(Request $request, $id)
    {
        $user = $request->user();
        
        $visaProcess = VisaProcess::with('application')
            ->find($id);

        if (!$visaProcess || $visaProcess->application->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Proceso de visa no encontrado',
            ], 404);
        }

        $timeline = $visaProcess->getTimeline();

        return response()->json([
            'success' => true,
            'data' => [
                'timeline' => $timeline,
                'current_status' => $visaProcess->current_status,
                'progress_percentage' => $visaProcess->getProgressPercentage(),
            ],
        ]);
    }

    /**
     * Get visa process history
     */
    public function history(Request $request, $id)
    {
        $user = $request->user();
        
        $visaProcess = VisaProcess::with('application')
            ->find($id);

        if (!$visaProcess || $visaProcess->application->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Proceso de visa no encontrado',
            ], 404);
        }

        $history = VisaStatusHistory::getFullHistory($id);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Get appointment details
     */
    public function appointment(Request $request, $id)
    {
        $user = $request->user();
        
        $visaProcess = VisaProcess::with('application')
            ->find($id);

        if (!$visaProcess || $visaProcess->application->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Proceso de visa no encontrado',
            ], 404);
        }

        if (!$visaProcess->appointment_date) {
            return response()->json([
                'success' => false,
                'message' => 'No hay cita programada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'appointment_date' => $visaProcess->appointment_date,
                'appointment_location' => $visaProcess->appointment_location,
                'days_until_appointment' => $visaProcess->getDaysUntilAppointment(),
                'interview_result' => $visaProcess->interview_result,
            ],
        ]);
    }

    /**
     * Get payment status
     */
    public function payments(Request $request, $id)
    {
        $user = $request->user();
        
        $visaProcess = VisaProcess::with('application')
            ->find($id);

        if (!$visaProcess || $visaProcess->application->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Proceso de visa no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'sevis' => [
                    'amount' => $visaProcess->sevis_amount,
                    'paid' => $visaProcess->sevis_paid_at !== null,
                    'paid_at' => $visaProcess->sevis_paid_at,
                    'sevis_id' => $visaProcess->sevis_id,
                ],
                'consular_fee' => [
                    'amount' => $visaProcess->consular_fee_amount,
                    'paid' => $visaProcess->consular_fee_paid_at !== null,
                    'paid_at' => $visaProcess->consular_fee_paid_at,
                ],
            ],
        ]);
    }

    /**
     * Get documents status
     */
    public function documents(Request $request, $id)
    {
        $user = $request->user();
        
        $visaProcess = VisaProcess::with('application')
            ->find($id);

        if (!$visaProcess || $visaProcess->application->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Proceso de visa no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'ds160' => [
                    'number' => $visaProcess->ds160_number,
                    'completed' => in_array($visaProcess->current_status, [
                        'ds160_completed', 'ds2019_pending', 'ds2019_received',
                        'sevis_paid', 'consular_fee_paid', 'appointment_scheduled',
                        'in_correspondence', 'visa_approved'
                    ]),
                ],
                'ds2019' => [
                    'number' => $visaProcess->ds2019_number,
                    'received' => in_array($visaProcess->current_status, [
                        'ds2019_received', 'sevis_paid', 'consular_fee_paid',
                        'appointment_scheduled', 'in_correspondence', 'visa_approved'
                    ]),
                ],
            ],
        ]);
    }

    /**
     * Get visa process statistics
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        
        $applications = Application::where('user_id', $user->id)
            ->with('visaProcess')
            ->get();

        $stats = [
            'total_processes' => 0,
            'in_progress' => 0,
            'approved' => 0,
            'rejected' => 0,
            'average_progress' => 0,
        ];

        $totalProgress = 0;

        foreach ($applications as $application) {
            if ($application->visaProcess) {
                $stats['total_processes']++;
                $totalProgress += $application->visaProcess->getProgressPercentage();

                switch ($application->visaProcess->current_status) {
                    case 'visa_approved':
                        $stats['approved']++;
                        break;
                    case 'visa_rejected':
                        $stats['rejected']++;
                        break;
                    default:
                        $stats['in_progress']++;
                }
            }
        }

        if ($stats['total_processes'] > 0) {
            $stats['average_progress'] = round($totalProgress / $stats['total_processes'], 2);
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
