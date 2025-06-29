<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Program;
use App\Models\Application;
use App\Models\Reward;
use App\Models\Redemption;
use App\Models\SupportTicket;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard del administrador.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Estadísticas para el dashboard
        $userCount = User::count();
        $pendingApplications = Application::where('status', 'pending')->count();
        $openTickets = SupportTicket::whereIn('status', ['open', 'in_progress'])->count();
        $pendingRedemptions = Redemption::where('status', 'pending')->count();
        
        // Solicitudes recientes
        $recentApplications = Application::with(['user', 'program'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        // Tickets recientes
        $recentTickets = SupportTicket::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        // Canjes recientes
        $recentRedemptions = Redemption::with(['user', 'reward'])
            ->orderBy('requested_at', 'desc')
            ->take(5)
            ->get();
        
        return view('admin.dashboard', compact(
            'userCount', 
            'pendingApplications', 
            'openTickets', 
            'pendingRedemptions',
            'recentApplications',
            'recentTickets',
            'recentRedemptions'
        ));
    }
}
