<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;

class AdminSupportTicketController extends Controller
{
    /**
     * Muestra la lista de tickets de soporte.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = SupportTicket::with('user');
        
        // Aplicar filtros si existen
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->has('status') && $request->input('status') != '') {
            $query->where('status', $request->input('status'));
        }
        
        if ($request->has('priority') && $request->input('priority') != '') {
            $query->where('priority', $request->input('priority'));
        }
        
        if ($request->has('date_range')) {
            $dateRange = explode(' - ', $request->input('date_range'));
            if (count($dateRange) == 2) {
                $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($dateRange[0]))->startOfDay();
                $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($dateRange[1]))->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }
        
        $tickets = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('admin.support.index', compact('tickets'));
    }

    /**
     * Muestra la información de un ticket de soporte específico.
     *
     * @param  \App\Models\SupportTicket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function show(SupportTicket $ticket)
    {
        // Cargar relaciones
        $ticket->load('user');
        
        // Obtener respuestas del ticket
        $replies = SupportTicketReply::where('ticket_id', $ticket->id)
                                    ->with('user')
                                    ->orderBy('created_at', 'asc')
                                    ->get();
        
        return view('admin.support.show', compact('ticket', 'replies'));
    }

    /**
     * Responde a un ticket de soporte.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SupportTicket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function reply(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'message' => 'required|string',
        ]);
        
        // Crear la respuesta
        SupportTicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);
        
        // Actualizar el estado del ticket
        $ticket->status = 'answered';
        $ticket->save();
        
        return redirect()->back()->with('success', 'Respuesta enviada correctamente.');
    }

    /**
     * Cambia el estado de un ticket de soporte.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SupportTicket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function changeStatus(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'status' => 'required|in:open,answered,closed',
        ]);
        
        $ticket->status = $request->status;
        $ticket->save();
        
        return redirect()->back()->with('success', 'Estado del ticket actualizado correctamente.');
    }

    /**
     * Cambia la prioridad de un ticket de soporte.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SupportTicket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function changePriority(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'priority' => 'required|in:low,medium,high',
        ]);
        
        $ticket->priority = $request->priority;
        $ticket->save();
        
        return redirect()->back()->with('success', 'Prioridad del ticket actualizada correctamente.');
    }

    /**
     * Asigna un ticket de soporte a un administrador.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SupportTicket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function assign(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);
        
        $ticket->assigned_to = $request->assigned_to;
        $ticket->save();
        
        return redirect()->back()->with('success', 'Ticket asignado correctamente.');
    }

    /**
     * Cierra un ticket de soporte.
     *
     * @param  \App\Models\SupportTicket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function close(SupportTicket $ticket)
    {
        $ticket->status = 'closed';
        $ticket->closed_at = now();
        $ticket->save();
        
        return redirect()->back()->with('success', 'Ticket cerrado correctamente.');
    }
    
    /**
     * Reabre un ticket de soporte cerrado.
     *
     * @param  \App\Models\SupportTicket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function reopen(SupportTicket $ticket)
    {
        if ($ticket->status !== 'closed') {
            return redirect()->back()->with('error', 'Solo se pueden reabrir tickets cerrados.');
        }
        
        $ticket->status = 'open';
        $ticket->closed_at = null;
        $ticket->save();
        
        return redirect()->back()->with('success', 'Ticket reabierto correctamente.');
    }
    
    /**
     * Exporta la lista de tickets de soporte a un archivo CSV.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        // Implementación básica para demostración
        return redirect()->route('admin.support.index')
            ->with('info', 'La funcionalidad de exportación estará disponible próximamente.');
    }
    
    /**
     * Muestra la lista de notificaciones del sistema.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function notifications(Request $request)
    {
        $query = \App\Models\Notification::with('user');
        
        // Aplicar filtros si existen
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->has('category') && $request->input('category') != '') {
            $query->where('category', $request->input('category'));
        }
        
        if ($request->has('is_read')) {
            $query->where('is_read', $request->input('is_read') == '1');
        }
        
        // Obtener categorías únicas para el filtro
        $categories = \App\Models\Notification::select('category')->distinct()->get()->pluck('category');
        
        // Obtener estadísticas
        $totalNotifications = \App\Models\Notification::count();
        $readNotifications = \App\Models\Notification::where('is_read', true)->count();
        $unreadNotifications = \App\Models\Notification::where('is_read', false)->count();
        
        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Obtener usuarios para el formulario de nueva notificación
        $users = \App\Models\User::where('role', '!=', 'admin')->orderBy('name')->get();
        
        return view('admin.notifications.index', compact('notifications', 'categories', 'totalNotifications', 'readNotifications', 'unreadNotifications', 'users'));
    }
    
    /**
     * Almacena una nueva notificación en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeNotification(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'category' => 'required|string|max:50',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);
        
        $count = 0;
        foreach ($request->user_ids as $userId) {
            \App\Models\Notification::create([
                'user_id' => $userId,
                'title' => $request->title,
                'message' => $request->message,
                'category' => $request->category,
                'is_read' => false
            ]);
            $count++;
        }
        
        return redirect()->route('admin.notifications.index')
            ->with('success', "Se han enviado {$count} notificaciones correctamente.");
    }
}
