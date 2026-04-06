<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobTracker;
use App\Models\JobComment;
use App\Models\User;
use App\Models\Division;
use Illuminate\Http\Request;

class JobApiController extends Controller
{
    /**
     * Semua karyawan bisa melihat SEMUA tugas aktif
     * Response: JSON Array langsung (bukan dibungkus object)
     */
    public function getActiveJobs(Request $request)
{
    $jobs = Job::with(['cs', 'technician', 'trackers', 'comments.user'])
        ->where('status', '!=', 'completed')
        ->latest()
        ->get();

    // SINKRONKAN: Bungkus dalam key 'data' agar sama dengan getJobHistory
    return response()->json([
        'success' => true,
        'data'    => $this->formatJobs($jobs)
    ]);
}

    /**
     * Semua karyawan bisa melihat SEMUA riwayat tugas selesai
     * Response: JSON Array langsung
     */
    public function getJobHistory(Request $request)
    {
        $jobs = Job::with(['cs', 'technician', 'trackers', 'comments.user'])
            ->where('status', 'completed')
            ->latest()
            ->get();

        // PENTING: Return langsung array, BUKAN { "success": true, "data": [...] }
        return response()->json($this->formatJobs($jobs));
    }

    /**
     * Terima tugas (hanya technician yang ditugaskan)
     */
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
            'job'     => $this->formatJob($job->load(['cs', 'trackers', 'comments.user'])),
        ]);
    }

        public function updateProgress(Request $request, $id)
    {
        $job = Job::findOrFail($id);
        $user = auth()->user(); 

        // 1. Validasi: Hanya teknisi yang ditunjuk yang bisa kerja
        if ($job->technician_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Maaf, hanya teknisi yang ditugaskan yang dapat memperbarui tugas ini.'
            ], 403);
        }

        // 2. Hitung langkah berikutnya
        $lastStep = \App\Models\JobTracker::where('job_id', $job->id)->max('step_number') ?? 0;
        $nextStep = $lastStep + 1;

        if ($lastStep >= 4) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas ini sudah selesai dikerjakan.'
            ], 400);
        }

        // 3. Olah Foto
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $fileName = time() . '_step' . $nextStep . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('job_photos'), $fileName);
            $photoPath = 'job_photos/' . $fileName;
        }

        // 4. Simpan ke Tracker
        \App\Models\JobTracker::create([
            'job_id' => $job->id,
            'step_number' => $nextStep,
            'description_value' => $request->description_value,
            'photo_path' => $photoPath,
        ]);

        // 5. Update status Job
        $job->update([
            'current_step' => $nextStep,
            'status' => ($nextStep >= 4) ? 'completed' : 'process'
        ]);

        // 6. Response (Gunakan formatJob agar Flutter tidak error saat parsing)
        return response()->json([
            'success' => true,
            'message' => "Langkah $nextStep berhasil diperbarui",
            // PENTING: Gunakan helper formatJob yang kamu punya di Controller
            'job' => $this->formatJob($job->load(['trackers', 'cs', 'technician', 'comments.user'])) 
        ]);
    }

    public function getTechnicians(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'kepala') {
            $csDiv = Division::where('name', 'Customer Service')->first();
            if (!$csDiv || $user->division_id !== $csDiv->id) {
                return response()->json(['error' => 'Tidak diizinkan'], 403);
            }
        }

        $technicians = User::where('role', 'karyawan')
            ->whereHas('division', function ($q) {
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

        // Return langsung array
        return response()->json($technicians);
    }

    /**
     * CS: Buat tugas baru
     */
    public function createJob(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'technician_id' => 'required|exists:users,id',
        ]);

        $user = $request->user();

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
     * Tambah komentar — SEMUA karyawan yang login boleh berkomentar
     */
    public function addComment(Request $request, $jobId)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $user = $request->user();
        $job  = Job::findOrFail($jobId);

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

    // ── Helper ────────────────────────────────────────────────────────────────

    private function formatJobs($jobs): array
    {
        return $jobs->map(fn($j) => $this->formatJob($j))->values()->toArray();
    }

    private function formatJob(Job $job): array
    {
        $trackers = ($job->trackers ?? collect())->map(function ($t) {
            return [
                'id'                => $t->id,
                'step_number'       => $t->step_number,
                'description_value' => $t->description_value,
                'photo_url'         => $t->photo_path ? asset($t->photo_path) : null,
                'video_url'         => $t->video_path ? asset($t->video_path) : null,
                'created_at'        => $t->created_at?->format('d M Y H:i'),
            ];
        })->values()->toArray();

        $comments = ($job->comments ?? collect())->map(fn($c) => [
            'id'         => $c->id,
            'comment'    => $c->comment,
            'user_name'  => $c->user->name ?? '-',
            'user_id'    => $c->user_id,
            'created_at' => $c->created_at?->format('d M Y H:i'),
        ])->values()->toArray();

        return [
            'id'           => $job->id,
            'title'        => $job->title,
            'description'  => $job->description,
            'status'       => $job->status,
            'current_step' => $job->current_step,
            'feedback'     => $job->feedback,
            'cs'           => $job->cs
                ? ['id' => $job->cs->id, 'name' => $job->cs->name]
                : null,
            'technician'   => $job->technician
                ? ['id' => $job->technician->id, 'name' => $job->technician->name]
                : null,
            'trackers'     => $trackers,
            'comments'     => $comments,
            'is_completed' => $job->status === 'completed',
            'is_process'   => $job->status === 'process',
            'is_pending'   => $job->status === 'pending',
            'created_at'   => $job->created_at?->format('d M Y'),
        ];
    }
}