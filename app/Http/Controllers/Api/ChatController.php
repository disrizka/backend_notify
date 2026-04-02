<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Chat;
use App\Models\User;
use App\Notifications\InternalNotification;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function index()
    {
        $chats = Chat::with(['user:id,name', 'parent.user:id,name'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($chats);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'file' => 'nullable|file|max:10240',
        ]);

        $path = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            // Buat nama file unik agar tidak bentrok
            $fileName = time() . '_' . $file->getClientOriginalName();
            
            // PINDAHKAN file langsung ke folder public/uploads (solusi 404 Windows)
            $file->move(public_path('uploads'), $fileName);
            
            // Simpan path relatifnya
            $path = 'uploads/' . $fileName; 
        }

        // 1. Simpan Chat ke Database
        $chat = Chat::create([
            'user_id'   => auth()->id(),
            'message'   => $request->message,
            'type'      => $request->type,
            'file_path' => $path, 
            'parent_id' => $request->parent_id,
        ]);

        // 2. LOGIKA NOTIFIKASI
        // Kirim notifikasi ke semua user selain pengirim (Group Chat Mode)
        // Jika ingin spesifik ke satu orang, ganti User::all() menjadi penerima saja.
        $users = User::where('id', '!=', auth()->id())->get();
        
        $notificationData = [
            'title'   => 'Pesan Baru dari ' . auth()->user()->name,
            'message' => $request->type == 'text' ? $request->message : 'Mengirim sebuah ' . $request->type,
            'type'    => 'chat'
        ];

        foreach ($users as $user) {
            $user->notify(new InternalNotification($notificationData));
        }

        return response()->json($chat->load('user'), 201);
    }

    /**
     * Stream video/audio dengan support Range Request
     * Digunakan untuk file yang berada di folder storage
     */
    public function stream(Request $request, $path)
    {
        $filePath = urldecode($path);
        
        // Cek apakah file ada di public/uploads dulu (karena kita pindah ke public)
        if (file_exists(public_path($filePath))) {
            $fullPath = public_path($filePath);
            $fileSize = filesize($fullPath);
            $mimeType = mime_content_type($fullPath);
        } else {
            // Backup ke storage disk jika tidak ada di public
            $disk = Storage::disk('public');
            if (!$disk->exists($filePath)) {
                return response()->json(['error' => 'File tidak ditemukan'], 404);
            }
            $fullPath = $disk->path($filePath);
            $fileSize = $disk->size($filePath);
            $mimeType = $disk->mimeType($filePath);
        }

        $start = 0;
        $end   = $fileSize - 1;

        $headers = [
            'Content-Type'                => $mimeType,
            'Accept-Ranges'               => 'bytes',
            'Cache-Control'               => 'no-cache, no-store',
            'Access-Control-Allow-Origin' => '*',
        ];

        if ($request->hasHeader('Range')) {
            $range = $request->header('Range');
            preg_match('/bytes=(\d+)-(\d*)/', $range, $matches);

            $start = intval($matches[1]);
            $end   = isset($matches[2]) && $matches[2] !== '' ? intval($matches[2]) : $fileSize - 1;
            $length = $end - $start + 1;

            $headers['Content-Range']  = "bytes $start-$end/$fileSize";
            $headers['Content-Length'] = $length;

            return response()->stream(function () use ($fullPath, $start, $length) {
                $fp = fopen($fullPath, 'rb');
                fseek($fp, $start);
                $remaining = $length;
                while (!feof($fp) && $remaining > 0) {
                    $chunk = min(8192, $remaining);
                    echo fread($fp, $chunk);
                    $remaining -= $chunk;
                    flush();
                }
                fclose($fp);
            }, 206, $headers);
        }

        $headers['Content-Length'] = $fileSize;

        return response()->stream(function () use ($fullPath) {
            $fp = fopen($fullPath, 'rb');
            while (!feof($fp)) {
                echo fread($fp, 8192);
                flush();
            }
            fclose($fp);
        }, 200, $headers);
    }
}