<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reward;
use Illuminate\Support\Facades\Storage;

class AdminRewardController extends Controller
{
    /**
     * Muestra la lista de recompensas.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Reward::query();
        
        // Aplicar filtros si existen
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('cost_min') && $request->input('cost_min') != '') {
            $query->where('cost', '>=', $request->input('cost_min'));
        }
        
        if ($request->has('cost_max') && $request->input('cost_max') != '') {
            $query->where('cost', '<=', $request->input('cost_max'));
        }
        
        if ($request->has('is_active') && $request->input('is_active') != '') {
            $query->where('is_active', $request->input('is_active'));
        }
        
        $rewards = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('admin.rewards.index', compact('rewards'));
    }

    /**
     * Muestra el formulario para crear una nueva recompensa.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.rewards.create');
    }

    /**
     * Almacena una nueva recompensa en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'cost' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        
        $reward = new Reward();
        $reward->name = $request->name;
        $reward->description = $request->description;
        $reward->cost = $request->cost;
        $reward->is_active = $request->is_active;
        $reward->stock = $request->stock;
        
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('rewards', 'public');
            $reward->image = $imagePath;
        }
        
        $reward->save();
        
        return redirect()->route('admin.rewards.index')
            ->with('success', 'Recompensa creada correctamente.');
    }

    /**
     * Muestra la información de una recompensa específica.
     *
     * @param  \App\Models\Reward  $reward
     * @return \Illuminate\Http\Response
     */
    public function show(Reward $reward)
    {
        // Cargar canjes relacionados con esta recompensa
        $redemptions = $reward->redemptions()->with('user')->paginate(10);
        
        // Estadísticas de canjes
        $redemptionStats = [
            'total' => $reward->redemptions()->count(),
            'pending' => $reward->redemptions()->where('status', 'pending')->count(),
            'approved' => $reward->redemptions()->where('status', 'approved')->count(),
            'rejected' => $reward->redemptions()->where('status', 'rejected')->count(),
        ];
        
        return view('admin.rewards.show', compact('reward', 'redemptions', 'redemptionStats'));
    }

    /**
     * Muestra el formulario para editar una recompensa.
     *
     * @param  \App\Models\Reward  $reward
     * @return \Illuminate\Http\Response
     */
    public function edit(Reward $reward)
    {
        return view('admin.rewards.edit', compact('reward'));
    }

    /**
     * Actualiza una recompensa en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reward  $reward
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Reward $reward)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'cost' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        
        $reward->name = $request->name;
        $reward->description = $request->description;
        $reward->cost = $request->cost;
        $reward->is_active = $request->is_active;
        $reward->stock = $request->stock;
        
        if ($request->hasFile('image')) {
            // Eliminar imagen anterior si existe
            if ($reward->image) {
                Storage::disk('public')->delete($reward->image);
            }
            
            $imagePath = $request->file('image')->store('rewards', 'public');
            $reward->image = $imagePath;
        }
        
        $reward->save();
        
        return redirect()->route('admin.rewards.index')
            ->with('success', 'Recompensa actualizada correctamente.');
    }

    /**
     * Elimina una recompensa de la base de datos.
     *
     * @param  \App\Models\Reward  $reward
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reward $reward)
    {
        // Verificar si hay canjes asociados
        if ($reward->redemptions()->count() > 0) {
            return redirect()->route('admin.rewards.index')
                ->with('error', 'No se puede eliminar la recompensa porque tiene canjes asociados.');
        }
        
        // Eliminar imagen si existe
        if ($reward->image) {
            Storage::disk('public')->delete($reward->image);
        }
        
        $reward->delete();
        
        return redirect()->route('admin.rewards.index')
            ->with('success', 'Recompensa eliminada correctamente.');
    }
    
    /**
     * Exporta la lista de recompensas a un archivo CSV.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        // Implementación básica para demostración
        return redirect()->route('admin.rewards.index')
            ->with('info', 'La funcionalidad de exportación estará disponible próximamente.');
    }
}
