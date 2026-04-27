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

 public function store(Request $request)
{
    $request->validate([
        'type'      => 'required|in:text,image,video,audio,voice,file',
        'message'   => 'nullable|string|max:5000',
        'file'      => 'nullable|file|max:51200',
        'parent_id' => 'nullable|integer|exists:chats,id',
    ]);
 
    $filePath = null;
    if ($request->hasFile('file') && $request->file('file')->isValid()) {
        $file     = $request->file('file');
        $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
        $file->move(public_path('uploads'), $filename);
        $filePath = 'uploads/' . $filename;
    }
 
    $sender = Auth::user();
 
    $chat = Chat::create([
        'user_id'   => $sender->id,
        'message'   => $request->input('message', ''),
        'type'      => $request->input('type', 'text'),
        'file_path' => $filePath,
        'parent_id' => $request->input('parent_id'),
        'is_pinned' => false,
        'is_edited' => false,
    ]);

    $otherUsers = \App\Models\User::where('id', '!=', $sender->id)->get();
    $messagePreview = '';
    if ($chat->message && $chat->message !== '') {
        $messagePreview = \Illuminate\Support\Str::limit($chat->message, 60);
    } elseif ($chat->type === 'image') {
        $messagePreview = '📷 Mengirim foto';
    } elseif ($chat->type === 'video') {
        $messagePreview = '🎥 Mengirim video';
    } elseif ($chat->type === 'audio' || $chat->type === 'voice') {
        $messagePreview = '🎵 Mengirim audio';
    } elseif ($chat->type === 'file') {
        $messagePreview = '📎 Mengirim file';
    }
 
    foreach ($otherUsers as $user) {
        $user->notify(new \App\Notifications\InternalNotification([
            'title'   => $sender->name,
            'message' => $messagePreview,
            'type'    => 'chat',
        ]));
    }
 
 
    if ($request->wantsJson() || $request->is('api/*')) {
        return response()->json($chat, 201);
    }
 
    return back();
}


public function update(Request $request, $id)
{
    $chat = Chat::findOrFail($id);

    if ((int)$chat->user_id !== (int)Auth::id()) {
        return request()->wantsJson() ? response()->json(['message' => 'Tidak diizinkan'], 403) : back();
    }

    $request->validate(['message' => 'required|string|max:5000']);

    $chat->update([
        'message'   => $request->input('message'),
        'is_edited' => true,
    ]);
    if ($request->wantsJson() || $request->is('api/*')) {
        return response()->json(['message' => 'Pesan diperbarui', 'chat' => $chat]);
    }

    return back()->with('success', 'Pesan berhasil diedit');
}

public function destroy($id)
{
    $chat = Chat::findOrFail($id);
    if ($chat->user_id !== Auth::id()) {
        return request()->wantsJson() ? response()->json(['message' => 'Tidak diizinkan'], 403) : back();
    }

    if ($chat->file_path && file_exists(public_path($chat->file_path))) {
        unlink(public_path($chat->file_path));
    }

    ChatSeen::where('chat_id', $id)->delete();
    $chat->delete();

    return (request()->wantsJson() || request()->is('api/*')) 
        ? response()->json(['message' => 'Pesan dihapus']) 
        : back();
}

public function pin($id)
{
    Chat::findOrFail($id)->update(['is_pinned' => true]);
    return (request()->wantsJson() || request()->is('api/*')) 
        ? response()->json(['message' => 'Pesan dipin', 'is_pinned' => true]) 
        : back();
}

public function unpin($id)
{
    Chat::findOrFail($id)->update(['is_pinned' => false]);
    return (request()->wantsJson() || request()->is('api/*')) 
        ? response()->json(['message' => 'Pin dihapus', 'is_pinned' => false]) 
        : back();
}


    public function markSeen($id)
    {
        $userId = Auth::id();
        ChatSeen::firstOrCreate(
            ['chat_id' => $id, 'user_id' => $userId],
            ['seen_at' => now()]
        );

        return response()->json(['message' => 'Ditandai dilihat']);
    }

    public function seenBy($id)
    {
        Chat::findOrFail($id);
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
