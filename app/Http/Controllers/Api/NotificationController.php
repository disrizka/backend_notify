<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Ambil daftar notifikasi (15 terbaru)
    public function index(Request $request)
{
    $notifications = $request->user()->notifications()->latest()->get()->map(function($n) {
        return [
            'id' => $n->id,
            'title' => $n->data['title'] ?? 'No Title',
            'message' => $n->data['message'] ?? '',
            'type' => $n->data['type'] ?? 'general',
            'is_read' => $n->read_at !== null,
            'created_at' => $n->created_at->diffForHumans(),
        ];
    });

    return response()->json($notifications); // Ini mengembalikan array []
}

    // Tandai semua sebagai dibaca
    public function markRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'Semua ditandai dibaca']);
    }

    // Ambil jumlah unread sekaligus pesan paling baru untuk popup
public function getUnreadCount(Request $request)
{
    $user = $request->user();
    
    // Ambil 1 notifikasi terbaru yang belum dibaca
    $latest = $user->unreadNotifications()->latest()->first();

    return response()->json([
        'unread_count' => $user->unreadNotifications()->count(),
        'latest_title' => $latest ? ($latest->data['title'] ?? 'Notifikasi Baru') : null,
        'latest_message' => $latest ? ($latest->data['message'] ?? '') : null,
    ]);
}
}