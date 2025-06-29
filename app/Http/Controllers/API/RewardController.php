<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use Illuminate\Http\Request;

class RewardController extends Controller
{
    /**
     * Display a listing of the active rewards.
     */
    public function index(Request $request)
    {
        $query = Reward::where('status', 'active');

        // Filter by category if provided
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        $rewards = $query->orderBy('cost', 'asc')->get();

        return response()->json($rewards);
    }

    /**
     * Display the specified reward.
     */
    public function show(string $id)
    {
        $reward = Reward::where('status', 'active')->findOrFail($id);
        return response()->json($reward);
    }

    /**
     * Get reward categories.
     */
    public function categories()
    {
        $categories = Reward::where('status', 'active')
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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
}
