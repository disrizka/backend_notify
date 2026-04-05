<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobTracker;
use App\Models\JobComment;
use App\Models\User;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobApiController extends Controller
{
    /**
 * 1. Semua Karyawan bisa melihat SEMUA tugas aktif (Transparansi)
 */
public function getActiveJobs(Request $request)
{
    // Hapus filter where('technician_id', $user->id) agar semua bisa lihat
    $jobs = Job::with(['cs', 'technician', 'trackers', 'comments.user'])
        ->where('status', '!=', 'completed')
        ->latest()
        ->get();

    return response()->json($this->formatJobs($jobs));
}


public function getJobHistory(Request $request)
{
    $jobs = Job::with(['cs', 'technician', 'trackers', 'comments.user'])
        ->where('status', 'completed')
        ->latest()
        ->get();

    return response()->json([
        'success' => true,
        'data'    => $this->formatJobs($jobs) 
    ]);
}

/**
 * 3. Izinkan SEMUA Karyawan memberi komentar
 */
public function addComment(Request $request, $jobId)
{
    $request->validate([
        'comment' => 'required|string|max:1000',
    ]);

    $user = $request->user();
    $job  = Job::findOrFail($jobId);

    // Sekarang semua karyawan yang sudah login boleh berkomentar
    $comment = JobComment::create([
        'job_id'  => $job->id,
        'user_id' => $user->id,
        'comment' => $request->comment,
    ]);

    return response()->json([
        'success' => true,
        'comment' => [
            'id'         => $comment->id,
            'comment'    => $comment->comment,
            'user_name'  => $user->name,
            'user_id'    => $user->id,
            'created_at' => $comment->created_at->format('d M Y H:i'),
        ],
    ], 201);
}
    public function acceptJob(Request $request, $jobId)
    {
        $job = Job::findOrFail($jobId);

        if ($job->technician_id !== $request->user()->id) {
            return response()->json(['error' => 'Bukan tugas Anda'], 403);
        }

        $job->update(['status' => 'process', 'current_step' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil diambil!',
            'job'     => $this->formatJob($job),
        ]);
    }

    /**
     * Update progress (technician)
     */
    public function updateProgress(Request $request, $jobId)
    {
        $job = Job::with('trackers')->findOrFail($jobId);

        if ($job->technician_id !== $request->user()->id) {
            return response()->json(['error' => 'Bukan tugas Anda'], 403);
        }

        $photoPath = null;
        $videoPath = null;

        if ($request->hasFile('photo')) {
            $file     = $request->file('photo');
            $fileName = time() . '_job_photo_' . $job->id . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('job_photos'), $fileName);
            $photoPath = 'job_photos/' . $fileName;
        }

        if ($request->hasFile('video')) {
            $file     = $request->file('video');
            $fileName = time() . '_job_video_' . $job->id . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('job_videos'), $fileName);
            $videoPath = 'job_videos/' . $fileName;
        }

        JobTracker::create([
            'job_id'            => $job->id,
            'step_number'       => $job->current_step,
            'description_value' => $request->description,
            'photo_path'        => $photoPath,
            'video_path'        => $videoPath,
        ]);

        $job->increment('current_step');
        $job->refresh();

        $completed = false;
        $message   = 'Tahap ' . ($job->current_step - 1) . ' berhasil disimpan!';

        if ($job->current_step > 4) {
            $job->update(['status' => 'completed']);
            $job->refresh();
            $completed = true;
            $message   = 'Selamat! Tugas telah selesai.';
        }

        return response()->json([
            'success'   => true,
            'completed' => $completed,
            'message'   => $message,
            'job'       => $this->formatJob($job->load(['cs', 'trackers', 'comments.user'])),
        ]);
    }

    /**
     * CS: Ambil daftar karyawan yang bisa diberi tugas (non-CS)
     */
    public function getTechnicians(Request $request)
    {
        $user = $request->user();

        // Hanya CS atau kepala yang bisa
        if ($user->role !== 'kepala') {
            $csDiv = Division::where('name', 'Customer Service')->first();
            if (!$csDiv || $user->division_id !== $csDiv->id) {
                return response()->json(['error' => 'Tidak diizinkan'], 403);
            }
        }

        $technicians = User::where('role', 'karyawan')
            ->whereHas('division', function($q) {
                $q->where('name', '!=', 'Customer Service');
            })
            ->with('division')
            ->get()
            ->map(fn($t) => [
                'id'       => $t->id,
                'name'     => $t->name,
                'email'    => $t->email,
                'division' => $t->division ? $t->division->name : '-',
            ]);

        return response()->json($technicians);
    }

    /**
     * CS: Buat tugas baru
     */
    public function createJob(Request $request)
    {
        $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'technician_id'  => 'required|exists:users,id',
        ]);

        $user = $request->user();

        // Validasi: hanya CS atau kepala
        if ($user->role !== 'kepala') {
            $csDiv = Division::where('name', 'Customer Service')->first();
            if (!$csDiv || $user->division_id !== $csDiv->id) {
                return response()->json(['error' => 'Hanya CS yang bisa membuat tugas'], 403);
            }
        }

        $job = Job::create([
            'title'         => $request->title,
            'description'   => $request->description,
            'cs_id'         => $user->id,
            'technician_id' => $request->technician_id,
            'status'        => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dikirim!',
            'job'     => $this->formatJob($job->load(['cs', 'trackers', 'comments.user'])),
        ], 201);
    }


    /**
     * Format jobs collection
     */
    private function formatJobs($jobs)
    {
        return $jobs->map(fn($j) => $this->formatJob($j));
    }

    /**
     * Format single job untuk response Flutter
     */
    private function formatJob(Job $job): array
    {
        $baseUrl = config('app.url');

        $trackers = $job->trackers->map(function($t) {
    return [
        'id'                => $t->id,
        'step_number'       => $t->step_number,
        'description_value' => $t->description_value,
        // asset() otomatis menambahkan domain/IP dari .env ke depan path
        'photo_url'         => $t->photo_path ? asset($t->photo_path) : null,
        'video_url'         => $t->video_path ? asset($t->video_path) : null,
        'created_at'        => $t->created_at?->format('d M Y H:i'),
    ];
});

        $comments = $job->comments->map(fn($c) => [
            'id'         => $c->id,
            'comment'    => $c->comment,
            'user_name'  => $c->user->name ?? '-',
            'user_id'    => $c->user_id,
            'created_at' => $c->created_at?->format('d M Y H:i'),
        ]);

        return [
            'id'             => $job->id,
            'title'          => $job->title,
            'description'    => $job->description,
            'status'         => $job->status,
            'current_step'   => $job->current_step,
            'feedback'       => $job->feedback,
            'cs'             => $job->cs ? ['id' => $job->cs->id, 'name' => $job->cs->name] : null,
            'technician'     => $job->technician
                ? ['id' => $job->technician->id, 'name' => $job->technician->name]
                : null,
            'trackers'       => $trackers,
            'comments'       => $comments,
            'is_completed'   => $job->status === 'completed',
            'is_process'     => $job->status === 'process',
            'is_pending'     => $job->status === 'pending',
            'created_at'     => $job->created_at?->format('d M Y'),
        ];
    }
}