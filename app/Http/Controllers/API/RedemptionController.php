<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Redemption;
use App\Models\Reward;
use App\Models\Point;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RedemptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $redemptions = Redemption::where('user_id', $user->id)
            ->with('reward')
            ->orderBy('requested_at', 'desc')
            ->get();
            
        return response()->json($redemptions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'reward_id' => 'required|exists:rewards,id'
        ]);

        $user = Auth::user();
        $reward = Reward::findOrFail($request->reward_id);

        // Verificar si el usuario tiene suficientes puntos
        $userPoints = Point::where('user_id', $user->id)->sum('change');
        
        if ($userPoints < $reward->cost) {
            return response()->json([
                'message' => 'No tienes suficientes puntos para este canje.'
            ], 400);
        }

        // Verificar si hay stock disponible
        if ($reward->stock !== null && $reward->stock <= 0) {
            return response()->json([
                'message' => 'No hay stock disponible para este premio.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Crear la redención
            $redemption = Redemption::create([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'points_cost' => $reward->cost,
                'status' => 'pending',
                'requested_at' => now()
            ]);

            // Deducir los puntos del usuario
            Point::create([
                'user_id' => $user->id,
                'change' => -$reward->cost,
                'reason' => 'redemption',
                'related_id' => $redemption->id
            ]);

            DB::commit();
            
            return response()->json([
                'message' => 'Canje solicitado exitosamente.',
                'redemption' => $redemption->load('reward')
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error al procesar el canje.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $redemption = Redemption::where('user_id', $user->id)
            ->where('id', $id)
            ->with('reward')
            ->firstOrFail();
            
        return response()->json($redemption);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Los usuarios no pueden actualizar sus canjes una vez solicitados
        return response()->json([
            'message' => 'No se pueden modificar canjes ya solicitados.'
        ], 403);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Los usuarios no pueden eliminar sus canjes
        return response()->json([
            'message' => 'No se pueden eliminar canjes ya solicitados.'
        ], 403);
    }
}
