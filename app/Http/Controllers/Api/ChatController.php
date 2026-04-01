<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Chat;
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
        try {
            $request->validate([
                'type' => 'required|string',
                'file' => 'nullable|file|max:102400', // max 100MB
            ]);

            $path = null;
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $disk = Storage::disk('public');

                // Pastikan folder uploads ada
                if (!$disk->exists('uploads')) {
                    $disk->makeDirectory('uploads');
                }

                $path = $file->store('uploads', 'public');

                // Validasi file benar-benar tersimpan
                if (!$disk->exists($path)) {
                    return response()->json(['error' => 'File gagal disimpan ke storage'], 500);
                }
            }

            $chat = Chat::create([
                'user_id'   => auth()->id(),
                'message'   => $request->message ?? '',
                'type'      => $request->type,
                'file_path' => $path,
                'parent_id' => $request->parent_id ?? null,
            ]);

            // WAJIB: load relasi agar Flutter tidak crash saat render
            $chat->load(['user:id,name', 'parent.user:id,name']);

            return response()->json($chat, 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error'   => 'Validasi gagal',
                'details' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('ChatController@store error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Stream video/audio dengan support Range Request
     * Wajib untuk video player di Android/iOS
     * Route: GET /api/stream/{path}
     */
    public function stream(Request $request, $path)
    {
        // Decode path (karena '/' di-encode jadi '%2F')
        $filePath = urldecode($path);
        $disk     = Storage::disk('public');

        if (!$disk->exists($filePath)) {
            return response()->json(['error' => 'File tidak ditemukan'], 404);
        }

        $fullPath = $disk->path($filePath);
        $fileSize = $disk->size($filePath);
        $mimeType = $disk->mimeType($filePath);

        // Handle Range Request (wajib untuk video streaming)
        $start = 0;
        $end   = $fileSize - 1;

        $headers = [
            'Content-Type'              => $mimeType,
            'Accept-Ranges'             => 'bytes',
            'Cache-Control'             => 'no-cache, no-store',
            'Access-Control-Allow-Origin' => '*',
        ];

        if ($request->hasHeader('Range')) {
            // Parse header Range: bytes=start-end
            $range = $request->header('Range');
            preg_match('/bytes=(\d+)-(\d*)/', $range, $matches);

            $start = intval($matches[1]);
            $end   = isset($matches[2]) && $matches[2] !== ''
                ? intval($matches[2])
                : $fileSize - 1;

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
            }, 206, $headers); // 206 Partial Content
        }

        // Request normal (bukan Range) — kirim seluruh file
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