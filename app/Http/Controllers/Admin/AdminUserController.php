<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

class AdminUserController extends Controller
{
    /**
     * Muestra la lista de usuarios.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Por defecto, solo mostrar administradores en esta vista
        $query = User::where('role', 'admin');
        
        // Aplicar filtros adicionales si existen
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('status') && $request->input('status') != '') {
            $isActive = $request->input('status') === 'active' ? 1 : 0;
            $query->where('is_active', $isActive);
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(10);
        
        $pageTitle = 'Administradores';
        
        return view('admin.users.index', compact('users', 'pageTitle'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Almacena un nuevo usuario en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'is_active' => $request->is_active,
            'phone' => $request->phone,
            'nationality' => $request->nationality,
            'address' => $request->address,
        ]);
        
        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Muestra la información de un usuario específico.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        // Cargar relaciones para mostrar en la vista
        $user->load([
            'applications.program', 
            'redemptions',
            'emergencyContacts',
            'workExperiences'
        ]);
        
        // Calcular estadísticas
        $applicationStats = [
            'total' => $user->applications->count(),
            'pending' => $user->applications->where('status', 'pending')->count(),
            'approved' => $user->applications->where('status', 'approved')->count(),
            'rejected' => $user->applications->where('status', 'rejected')->count(),
        ];
        
        $redemptionStats = [
            'total' => $user->redemptions->count(),
            'pending' => $user->redemptions->where('status', 'pending')->count(),
            'approved' => $user->redemptions->where('status', 'approved')->count(),
            'rejected' => $user->redemptions->where('status', 'rejected')->count(),
        ];
        
        // Calcular puntos totales
        $totalPoints = $user->points->sum('change');
        
        return view('admin.users.show', compact('user', 'applicationStats', 'redemptionStats', 'totalPoints'));
    }

    /**
     * Muestra el formulario para editar un usuario.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Actualiza un usuario en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }
        $user->role = $request->role;
        $user->phone = $request->phone;
        $user->nationality = $request->nationality;
        $user->address = $request->address;
        $user->save();
        
        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Elimina un usuario de la base de datos.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        // Verificar que no se está eliminando a sí mismo
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No puedes eliminar tu propio usuario.');
        }
        
        $user->delete();
        
        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }
    
    /**
     * Exporta la lista de usuarios a un archivo CSV.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        // Implementación básica para demostración
        return redirect()->route('admin.users.index')
            ->with('info', 'La funcionalidad de exportación estará disponible próximamente.');
    }
}
