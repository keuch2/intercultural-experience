<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\JobOffer;
use App\Models\JobOfferReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JobOfferReservationController extends Controller
{
    /**
     * Display user's reservations
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $reservations = JobOfferReservation::with(['jobOffer.sponsor', 'jobOffer.hostCompany', 'application'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reservations,
        ]);
    }

    /**
     * Create a new reservation
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Verificar si ya tiene una reserva activa
        if (JobOfferReservation::hasActiveReservation($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tienes una reserva activa. Debes cancelarla antes de hacer una nueva.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'job_offer_id' => 'required|exists:job_offers,id',
            'application_id' => 'nullable|exists:applications,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $jobOffer = JobOffer::find($request->job_offer_id);

        // Verificar disponibilidad
        if (!$jobOffer->hasAvailableSlots()) {
            return response()->json([
                'success' => false,
                'message' => 'Esta oferta laboral ya no tiene cupos disponibles',
            ], 409);
        }

        DB::beginTransaction();
        try {
            // Reservar el cupo
            if (!$jobOffer->reserveSlot()) {
                throw new \Exception('No se pudo reservar el cupo');
            }

            // Crear la reserva
            $reservation = JobOfferReservation::create([
                'user_id' => $user->id,
                'job_offer_id' => $request->job_offer_id,
                'application_id' => $request->application_id,
                'reservation_fee' => 800.00,
                'cancellation_fee' => 100.00,
                'status' => 'reserved',
                'reserved_at' => now(),
                'fee_paid' => false,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reserva creada exitosamente',
                'data' => $reservation->load(['jobOffer.sponsor', 'jobOffer.hostCompany']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la reserva: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified reservation
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $reservation = JobOfferReservation::with(['jobOffer.sponsor', 'jobOffer.hostCompany', 'application'])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reserva no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $reservation,
        ]);
    }

    /**
     * Confirm a reservation
     */
    public function confirm(Request $request, $id)
    {
        $user = $request->user();
        
        $reservation = JobOfferReservation::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reserva no encontrada',
            ], 404);
        }

        if (!$reservation->confirm()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede confirmar esta reserva. Estado actual: ' . $reservation->status,
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reserva confirmada exitosamente',
            'data' => $reservation->fresh(),
        ]);
    }

    /**
     * Cancel a reservation
     */
    public function cancel(Request $request, $id)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $reservation = JobOfferReservation::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reserva no encontrada',
            ], 404);
        }

        DB::beginTransaction();
        try {
            if (!$reservation->cancel($user->id, $request->reason)) {
                throw new \Exception('No se puede cancelar esta reserva. Estado actual: ' . $reservation->status);
            }

            $refundAmount = $reservation->calculateRefundAmount();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reserva cancelada exitosamente',
                'data' => [
                    'reservation' => $reservation->fresh(),
                    'refund_amount' => $refundAmount,
                    'cancellation_fee' => $reservation->cancellation_fee,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Mark reservation as paid
     */
    public function markAsPaid(Request $request, $id)
    {
        $user = $request->user();
        
        $reservation = JobOfferReservation::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reserva no encontrada',
            ], 404);
        }

        $reservation->markAsPaid();

        return response()->json([
            'success' => true,
            'message' => 'Pago registrado exitosamente',
            'data' => $reservation->fresh(),
        ]);
    }

    /**
     * Get active reservation
     */
    public function active(Request $request)
    {
        $user = $request->user();
        
        $reservation = JobOfferReservation::with(['jobOffer.sponsor', 'jobOffer.hostCompany'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['reserved', 'confirmed'])
            ->first();

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes reservas activas',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $reservation,
        ]);
    }
}
