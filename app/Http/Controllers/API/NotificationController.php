<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Notificaciones in-app del usuario autenticado (campana de la app).
 *
 *  GET    /api/notifications[?unread=true]
 *  GET    /api/notifications/unread-count
 *  GET    /api/notifications/{id}
 *  PATCH  /api/notifications/{id}/read     (también PUT /api/notifications/{id})
 *  PATCH  /api/notifications/mark-all-read
 *  DELETE /api/notifications/{id}
 *  POST   /api/admin/notifications         (admin: enviar a un usuario o a todos)
 */
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::where('user_id', $request->user()->id)->orderByDesc('created_at');
        if ($request->boolean('unread')) {
            $query->where('is_read', false);
        }

        return response()->json(['status' => 'success', 'data' => $query->limit(100)->get()->map(fn ($n) => $this->serialize($n))]);
    }

    public function unreadCount(Request $request)
    {
        return response()->json(['status' => 'success', 'count' => Notification::where('user_id', $request->user()->id)->where('is_read', false)->count()]);
    }

    public function show(Request $request, string $id)
    {
        $n = Notification::where('user_id', $request->user()->id)->findOrFail($id);

        return response()->json(['status' => 'success', 'data' => $this->serialize($n)]);
    }

    public function markAsRead(Request $request, string $id)
    {
        $n = Notification::where('user_id', $request->user()->id)->findOrFail($id);
        $n->update(['is_read' => true]);

        return response()->json(['status' => 'success', 'data' => $this->serialize($n)]);
    }

    public function update(Request $request, string $id)
    {
        return $this->markAsRead($request, $id);
    }

    public function markAllAsRead(Request $request)
    {
        $count = Notification::where('user_id', $request->user()->id)->where('is_read', false)->update(['is_read' => true]);

        return response()->json(['status' => 'success', 'data' => ['count' => $count]]);
    }

    public function destroy(Request $request, string $id)
    {
        Notification::where('user_id', $request->user()->id)->findOrFail($id)->delete();

        return response()->json(['status' => 'success']);
    }

    /** Admin: enviar una notificación manual a un usuario o a todos los participantes. */
    public function store(Request $request)
    {
        abort_unless($request->user()?->role === 'admin', 403);
        $data = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'category' => 'nullable|string|max:100',
        ]);
        $targets = $data['user_id'] ? [$data['user_id']] : User::where('role', 'user')->pluck('id')->all();
        foreach ($targets as $userId) {
            Notification::create(['user_id' => $userId, 'title' => $data['title'], 'message' => $data['message'], 'category' => $data['category'] ?? 'general', 'is_read' => false, 'created_at' => now()]);
        }

        return response()->json(['status' => 'success', 'data' => ['sent' => count($targets)]], 201);
    }

    private function serialize(Notification $n): array
    {
        return [
            'id' => $n->id,
            'user_id' => $n->user_id,
            'title' => $n->title,
            'message' => $n->message,
            'category' => $n->category,
            'type' => match ($n->category) {
                'job_pool' => 'success', 'program_stage' => 'info', 'payment' => 'warning', 'program_dates' => 'warning', default => 'info'
            },
            'is_read' => (bool) $n->is_read,
            'created_at' => optional($n->created_at)->toIso8601String(),
            'updated_at' => optional($n->created_at)->toIso8601String(),
        ];
    }
}
