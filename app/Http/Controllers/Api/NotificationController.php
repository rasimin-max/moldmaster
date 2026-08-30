<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);
        return response()->json([
            'data' => $notifications->map(fn($n) => [
                'id' => $n->id,
                'type' => $n->type,
                'data' => $n->data,
                'read_at' => $n->read_at,
                'created_at' => $n->created_at,
            ]),
            'unread_count' => $request->user()->unreadNotifications()->count(),
            'meta' => ['total' => $notifications->total()],
        ]);
    }

    public function markRead(string $id, Request $request): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['message' => 'Notifikasi ditandai sebagai dibaca.']);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'Semua notifikasi ditandai dibaca.']);
    }
}
