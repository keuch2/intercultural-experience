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
use App\Models\AuPairDocument;
use App\Models\Payment;

class AdminDashboardController extends Controller
{
    /**
     * Muestra el dashboard del administrador.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Módulo 13: Differentiated user metrics
        $userCount = User::count();
        $participantCount = User::where('role', 'user')->count();
        $adminCount = User::where('role', 'admin')->count();
        $agentCount = User::where('role', 'agent')->count();

        // Au Pair specific metrics
        $auPairCount = User::where('role', 'user')
            ->whereHas('applications', fn($q) => $q->whereHas('program', fn($pq) => $pq->where('subcategory', 'Au Pair')))
            ->count();
        $auPairActiveCount = \App\Models\AuPairProcess::whereIn('current_stage', ['application', 'match_visa'])->count();
        $auPairAdmissionCount = \App\Models\AuPairProcess::where('current_stage', 'admission')->count();
        
        // Centralizar todas las solicitudes pendientes de aprobación
        $pendingApps = Application::where('status', 'pending')->count();
        $pendingDocs = AuPairDocument::where('status', 'pending')->count();
        $pendingPayments = Payment::where('status', 'pending')->count();
        $pendingApplications = $pendingApps + $pendingDocs + $pendingPayments;
        
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
            'participantCount',
            'adminCount',
            'agentCount',
            'auPairCount',
            'auPairActiveCount',
            'auPairAdmissionCount',
            'pendingApplications',
            'pendingApps',
            'pendingDocs',
            'pendingPayments',
            'openTickets',
            'pendingRedemptions',
            'recentApplications',
            'recentTickets',
            'recentRedemptions'
        ));
    }
}
