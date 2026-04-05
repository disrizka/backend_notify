<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Mengambil semua notifikasi user.
     */
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->get()->map(function($n) {
            return [
                'id'         => $n->id,
                'title'      => $n->data['title'] ?? 'No Title',
                'message'    => $n->data['message'] ?? '',
                'type'       => $n->data['type'] ?? 'general',
                'is_read'    => $n->read_at !== null, // Hapus tulisan syntax error di sini
                'created_at' => $n->created_at->diffForHumans(),
            ];
        });

        return response()->json($notifications);
    }

    /**
     * Menandai semua notifikasi yang belum dibaca menjadi sudah dibaca.
     */
    public function markRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'Semua ditandai dibaca']);
    }

    /**
     * Mengambil jumlah notifikasi yang belum dibaca dan data terbaru.
     */
    public function getUnreadCount(Request $request)
    {
        $user = $request->user();
        
        $latest = $user->unreadNotifications()->latest()->first();

        return response()->json([
            'unread_count'   => $user->unreadNotifications()->count(),
            'latest_title'   => $latest ? ($latest->data['title'] ?? null) : null,
            'latest_message' => $latest ? ($latest->data['message'] ?? null) : null,
        ]);
    }
}