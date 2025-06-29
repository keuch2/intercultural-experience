<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Point;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PointController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Get user's points balance.
     */
    public function balance()
    {
        $user = Auth::user();
        
        $total = Point::where('user_id', $user->id)->sum('change');
        $earned = Point::where('user_id', $user->id)
            ->where('change', '>', 0)
            ->sum('change');
        $spent = abs(Point::where('user_id', $user->id)
            ->where('change', '<', 0)
            ->sum('change'));

        // Get pending redemptions points (if any)
        $pending = 0; // Could be calculated based on pending redemptions

        return response()->json([
            'total' => $total,
            'earned' => $earned,
            'spent' => $spent,
            'pending' => $pending
        ]);
    }

    /**
     * Get user's points history.
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        
        $query = Point::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Pagination
        $perPage = $request->get('per_page', 20);
        $points = $query->paginate($perPage);

        return response()->json($points);
    }

    /**
     * Get points statistics.
     */
    public function statistics()
    {
        $user = Auth::user();
        
        $stats = [
            'total_earned' => Point::where('user_id', $user->id)
                ->where('change', '>', 0)
                ->sum('change'),
            'total_spent' => abs(Point::where('user_id', $user->id)
                ->where('change', '<', 0)
                ->sum('change')),
            'transactions_count' => Point::where('user_id', $user->id)->count(),
            'current_balance' => Point::where('user_id', $user->id)->sum('change'),
        ];

        // Get points by reason
        $by_reason = Point::where('user_id', $user->id)
            ->selectRaw('reason, SUM(change) as total, COUNT(*) as count')
            ->groupBy('reason')
            ->get();

        $stats['by_reason'] = $by_reason;

        return response()->json($stats);
    }
}
