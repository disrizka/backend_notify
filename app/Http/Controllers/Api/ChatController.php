<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatSeen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    public function index()
    {
        $chats = Chat::with(['user', 'parent.user'])
            ->withCount('seenBy')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($chat) {
                return [
                    'id'            => $chat->id,
                    'user_id'       => $chat->user_id,
                    'user'          => $chat->user ? ['id' => $chat->user->id, 'name' => $chat->user->name] : null,
                    'message'       => $chat->message,
                    'type'          => $chat->type,
                    'file_path'     => $chat->file_path,
                    'parent_id'     => $chat->parent_id,
                    'parent'        => $chat->parent ? [
                        'id'      => $chat->parent->id,
                        'message' => $chat->parent->message,
                        'user'    => $chat->parent->user ? ['name' => $chat->parent->user->name] : null,
                    ] : null,
                    'is_pinned'     => (bool) $chat->is_pinned,
                    'is_edited'     => (bool) $chat->is_edited,
                    'seen_by_count' => $chat->seen_by_count,
                    'created_at'    => $chat->created_at->toIso8601String(),
                    'updated_at'    => $chat->updated_at->toIso8601String(),
                ];
            });

        return response()->json($chats);
    }

    // ── POST /api/chats ───────────────────────────────────────────────────────
    // Kirim pesan teks atau file (image/video/audio/file).
    public function store(Request $request)
    {
        $request->validate([
            'type'      => 'required|in:text,image,video,audio,voice,file',
            'message'   => 'nullable|string|max:5000',
            'file'      => 'nullable|file|max:51200', // max 50 MB
            'parent_id' => 'nullable|integer|exists:chats,id',
        ]);

        $filePath = null;

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            // Simpan langsung ke public/uploads agar akses via /uploads/namafile
            $file     = $request->file('file');
            $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $file->move(public_path('uploads'), $filename);
            $filePath = 'uploads/' . $filename;
        }

        $chat = Chat::create([
            'user_id'   => Auth::id(),
            'message'   => $request->input('message', ''),
            'type'      => $request->input('type', 'text'),
            'file_path' => $filePath,
            'parent_id' => $request->input('parent_id'),
            'is_pinned' => false,
            'is_edited' => false,
        ]);

        $chat->load(['user', 'parent.user']);

        return response()->json([
            'id'         => $chat->id,
            'user_id'    => $chat->user_id,
            'user'       => $chat->user ? ['id' => $chat->user->id, 'name' => $chat->user->name] : null,
            'message'    => $chat->message,
            'type'       => $chat->type,
            'file_path'  => $chat->file_path,
            'parent_id'  => $chat->parent_id,
            'parent'     => $chat->parent ? [
                'id'      => $chat->parent->id,
                'message' => $chat->parent->message,
                'user'    => $chat->parent->user ? ['name' => $chat->parent->user->name] : null,
            ] : null,
            'is_pinned'  => false,
            'is_edited'  => false,
            'created_at' => $chat->created_at->toIso8601String(),
        ], 201);
    }

    // ── PUT /api/chats/{id} ───────────────────────────────────────────────────
    // Edit isi teks pesan. Hanya pemilik pesan yang boleh.
    public function update(Request $request, $id)
    {
        $chat = Chat::findOrFail($id);

        if ($chat->user_id !== Auth::id()) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

        $request->validate(['message' => 'required|string|max:5000']);

        $chat->update([
            'message'   => $request->input('message'),
            'is_edited' => true,
        ]);

        return response()->json(['message' => 'Pesan diperbarui', 'chat' => $chat]);
    }

    // ── DELETE /api/chats/{id} ────────────────────────────────────────────────
    // Hapus pesan + file terkait. Hanya pemilik pesan yang boleh.
    public function destroy($id)
    {
        $chat = Chat::findOrFail($id);

        if ($chat->user_id !== Auth::id()) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

        // Hapus file dari public/uploads jika ada
        if ($chat->file_path) {
            $fullPath = public_path($chat->file_path);
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        // Hapus seen records
        ChatSeen::where('chat_id', $id)->delete();

        $chat->delete();

        return response()->json(['message' => 'Pesan dihapus']);
    }

    // ── POST /api/chats/{id}/pin ──────────────────────────────────────────────
    // Pin sebuah pesan (semua user boleh pin).
    public function pin($id)
    {
        $chat = Chat::findOrFail($id);
        $chat->update(['is_pinned' => true]);
        return response()->json(['message' => 'Pesan dipin', 'is_pinned' => true]);
    }

    // ── POST /api/chats/{id}/unpin ────────────────────────────────────────────
    // Hapus pin dari pesan.
    public function unpin($id)
    {
        $chat = Chat::findOrFail($id);
        $chat->update(['is_pinned' => false]);
        return response()->json(['message' => 'Pin dihapus', 'is_pinned' => false]);
    }

    // ── POST /api/chats/{id}/seen ─────────────────────────────────────────────
    // Tandai pesan sudah dilihat oleh user saat ini.
    public function markSeen($id)
    {
        $userId = Auth::id();

        // Cegah duplikat
        ChatSeen::firstOrCreate(
            ['chat_id' => $id, 'user_id' => $userId],
            ['seen_at' => now()]
        );

        return response()->json(['message' => 'Ditandai dilihat']);
    }

    // ── GET /api/chats/{id}/seen ──────────────────────────────────────────────
    // Kembalikan daftar user yang sudah melihat pesan ini.
    public function seenBy($id)
    {
        Chat::findOrFail($id); // 404 jika tidak ada

        $seenList = ChatSeen::where('chat_id', $id)
            ->with('user:id,name')
            ->orderBy('seen_at', 'asc')
            ->get()
            ->map(fn($s) => [
                'id'      => $s->user->id ?? null,
                'name'    => $s->user->name ?? 'Unknown',
                'seen_at' => $s->seen_at ? $s->seen_at->toIso8601String() : null,
            ]);

        return response()->json($seenList);
    }
}
