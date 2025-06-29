<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Redemption;
use App\Models\Reward;
use App\Models\Point;
use App\Models\User;

class AdminRedemptionController extends Controller
{
    /**
     * Muestra la lista de canjes.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Redemption::with(['user', 'reward']);
        
        // Aplicar filtros si existen
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('reward', function($rewardQuery) use ($search) {
                      $rewardQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->has('reward_id') && $request->input('reward_id') != '') {
            $query->where('reward_id', $request->input('reward_id'));
        }
        
        if ($request->has('status') && $request->input('status') != '') {
            $query->where('status', $request->input('status'));
        }
        
        if ($request->has('date_range')) {
            $dateRange = explode(' - ', $request->input('date_range'));
            if (count($dateRange) == 2) {
                $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($dateRange[0]))->startOfDay();
                $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($dateRange[1]))->endOfDay();
                $query->whereBetween('requested_at', [$startDate, $endDate]);
            }
        }
        
        $redemptions = $query->orderBy('requested_at', 'desc')->paginate(10);
        $rewards = Reward::all();
        
        return view('admin.redemptions.index', compact('redemptions', 'rewards'));
    }

    /**
     * Muestra la información de un canje específico.
     *
     * @param  \App\Models\Redemption  $redemption
     * @return \Illuminate\Http\Response
     */
    public function show(Redemption $redemption)
    {
        // Cargar relaciones
        $redemption->load(['user', 'reward']);
        
        // Obtener el historial de puntos del usuario
        $pointsHistory = Point::where('user_id', $redemption->user_id)
                             ->orderBy('created_at', 'desc')
                             ->limit(10)
                             ->get();
        
        // Calcular el balance de puntos del usuario
        $pointsBalance = Point::where('user_id', $redemption->user_id)
                             ->sum('change');
        
        return view('admin.redemptions.show', compact('redemption', 'pointsHistory', 'pointsBalance'));
    }

    /**
     * Aprueba un canje.
     *
     * @param  \App\Models\Redemption  $redemption
     * @return \Illuminate\Http\Response
     */
    public function approve(Request $request, Redemption $redemption)
    {
        if ($redemption->status !== 'pending') {
            return redirect()->back()->with('error', 'Solo se pueden aprobar canjes pendientes.');
        }
        
        // Verificar si hay suficiente stock
        $reward = Reward::find($redemption->reward_id);
        if ($reward->stock !== null && $reward->stock <= 0) {
            return redirect()->back()->with('error', 'No hay suficiente stock para este premio.');
        }
        
        // Actualizar el estado del canje
        $redemption->status = 'approved';
        $redemption->resolved_at = now();
        $redemption->admin_notes = $request->input('admin_notes');
        $redemption->save();
        
        // Actualizar el stock de la recompensa si es necesario
        if ($reward->stock !== null) {
            $reward->stock -= 1;
            $reward->save();
        }
        
        return redirect()->back()->with('success', 'El canje ha sido aprobado.');
    }

    /**
     * Rechaza un canje.
     *
     * @param  \App\Models\Redemption  $redemption
     * @return \Illuminate\Http\Response
     */
    public function reject(Request $request, Redemption $redemption)
    {
        if ($redemption->status !== 'pending') {
            return redirect()->back()->with('error', 'Solo se pueden rechazar canjes pendientes.');
        }
        
        // Actualizar el estado del canje
        $redemption->status = 'rejected';
        $redemption->resolved_at = now();
        $redemption->admin_notes = $request->input('admin_notes');
        $redemption->save();
        
        // Devolver los puntos al usuario
        Point::create([
            'user_id' => $redemption->user_id,
            'change' => $redemption->points_cost,
            'reason' => 'redemption_rejected',
            'related_id' => $redemption->id
        ]);
        
        return redirect()->back()->with('success', 'El canje ha sido rechazado y los puntos han sido devueltos al usuario.');
    }

    /**
     * Actualiza la información de entrega de un canje.
     *
     * @param  \App\Models\Redemption  $redemption
     * @return \Illuminate\Http\Response
     */
    public function updateDelivery(Request $request, Redemption $redemption)
    {
        if ($redemption->status !== 'approved') {
            return redirect()->back()->with('error', 'Solo se puede actualizar la información de entrega de canjes aprobados.');
        }
        
        $request->validate([
            'tracking_number' => 'nullable|string|max:255',
            'carrier' => 'nullable|string|max:255',
            'delivery_notes' => 'nullable|string'
        ]);
        
        $redemption->tracking_number = $request->input('tracking_number');
        $redemption->carrier = $request->input('carrier');
        $redemption->delivery_notes = $request->input('delivery_notes');
        
        // Si se proporciona información de entrega, marcar como entregado
        if ($request->filled('tracking_number') || $request->filled('carrier')) {
            $redemption->status = 'delivered';
            $redemption->delivered_at = now();
        }
        
        $redemption->save();
        
        return redirect()->back()->with('success', 'La información de entrega ha sido actualizada.');
    }

    /**
     * Marca un canje como entregado.
     *
     * @param  \App\Models\Redemption  $redemption
     * @return \Illuminate\Http\Response
     */
    public function deliver(Redemption $redemption)
    {
        if ($redemption->status !== 'approved') {
            return redirect()->back()->with('error', 'Solo se pueden marcar como entregados los canjes aprobados.');
        }
        
        $redemption->status = 'delivered';
        $redemption->delivered_at = now();
        $redemption->save();
        
        return redirect()->back()->with('success', 'El canje ha sido marcado como entregado.');
    }

    /**
     * Añade una nota a un canje.
     *
     * @param  \App\Models\Redemption  $redemption
     * @return \Illuminate\Http\Response
     */
    public function storeNote(Request $request, Redemption $redemption)
    {
        $request->validate([
            'note' => 'required|string'
        ]);
        
        // Por simplicidad, añadimos la nota al campo admin_notes
        // En una implementación más completa, se podría crear una tabla separada para notas
        $existingNotes = $redemption->admin_notes ? $redemption->admin_notes . "\n\n" : '';
        $newNote = "[" . now()->format('Y-m-d H:i:s') . "] " . auth()->user()->name . ": " . $request->input('note');
        
        $redemption->admin_notes = $existingNotes . $newNote;
        $redemption->save();
        
        return redirect()->back()->with('success', 'Nota añadida exitosamente.');
    }

    /**
     * Elimina una nota de un canje.
     *
     * @param  int  $noteId
     * @return \Illuminate\Http\Response
     */
    public function destroyNote($noteId)
    {
        // Para simplicidad, este método no hace nada ya que las notas están en un solo campo
        // En una implementación completa, se eliminaría de una tabla de notas separada
        return redirect()->back()->with('info', 'Las notas no se pueden eliminar individualmente en esta versión.');
    }
    
    /**
     * Exporta la lista de canjes a un archivo CSV.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        // Implementación básica para demostración
        return redirect()->route('admin.redemptions.index')
            ->with('info', 'La funcionalidad de exportación estará disponible próximamente.');
    }
    
    /**
     * Muestra la lista de transacciones de puntos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function points(Request $request)
    {
        $query = Point::with('user');
        
        // Aplicar filtros si existen
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->has('reason') && $request->input('reason') != '') {
            $query->where('reason', $request->input('reason'));
        }
        
        if ($request->has('date_range')) {
            $dateRange = explode(' - ', $request->input('date_range'));
            if (count($dateRange) == 2) {
                $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($dateRange[0]))->startOfDay();
                $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($dateRange[1]))->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }
        
        // Obtener razones únicas para el filtro
        $reasons = Point::select('reason')->distinct()->get()->pluck('reason');
        
        // Obtener estadísticas
        $totalPoints = Point::sum('change');
        $positivePoints = Point::where('change', '>', 0)->sum('change');
        $negativePoints = Point::where('change', '<', 0)->sum('change');
        
        // Obtener usuarios con más puntos
        $topUsers = User::withSum('points as total_points', 'change')
            ->orderByDesc('total_points')
            ->limit(5)
            ->get();
        
        $points = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.points.index', compact('points', 'reasons', 'totalPoints', 'positivePoints', 'negativePoints', 'topUsers'));
    }
}
