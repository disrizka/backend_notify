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
     * Mengambil semua tugas yang belum selesai (Pending & Process)
     */
    public function getActiveJobs()
    {
        $jobs = Job::with(['cs', 'technician', 'trackers', 'comments.user'])
            ->where('status', '!=', 'completed')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $this->formatJobs($jobs)
        ]);
    }

    /**
     * Detail satu tugas (Untuk Refresh di Flutter)
     */
    public function show($id)
    {
        $job = Job::with(['cs', 'technician', 'trackers', 'comments.user'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'job' => $this->formatJob($job)
        ]);
    }

    /**
     * Mengambil semua tugas yang sudah selesai
     */
    public function getJobHistory()
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
     * Teknisi mengambil tugas pending
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

    /**
     * Teknisi upload progress (Foto & Video)
     */
    public function updateProgress(Request $request, $id)
    {
        $job = Job::findOrFail($id);
        $user = auth()->user(); 

        if ($job->technician_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Hanya teknisi pelaksana yang bisa update'], 403);
        }

        $nextStep = ($job->current_step ?? 0) + 1;

        if ($nextStep > 4) {
            return response()->json(['success' => false, 'message' => 'Tugas sudah selesai'], 400);
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $fileName = time() . '_step' . $nextStep . '.' . $request->file('photo')->getClientOriginalExtension();
            $request->file('photo')->move(public_path('job_photos'), $fileName);
            $photoPath = 'job_photos/' . $fileName;
        }

        $videoPath = null;
        if ($request->hasFile('video')) {
            $vName = time() . '_video_step' . $nextStep . '.' . $request->file('video')->getClientOriginalExtension();
            $request->file('video')->move(public_path('job_videos'), $vName);
            $videoPath = 'job_videos/' . $vName;
        }

        JobTracker::create([
            'job_id' => $job->id,
            'step_number' => $nextStep,
            'description_value' => $request->description_value,
            'photo_path' => $photoPath,
            'video_path' => $videoPath,
        ]);

        $job->update([
            'current_step' => $nextStep,
            'status' => ($nextStep >= 4) ? 'completed' : 'process'
        ]);

        return response()->json([
            'success' => true,
            'message' => "Langkah $nextStep berhasil diperbarui",
            'job' => $this->formatJob($job->load(['trackers', 'cs', 'technician', 'comments.user'])) 
        ]);
    }

    /**
     * Dropdown list teknisi untuk CS
     */
    public function getTechnicians()
    {
        $technicians = User::where('role', 'karyawan')
            ->whereHas('division', function ($q) {
                $q->where('name', '!=', 'Customer Service');
            })
            ->get(['id', 'name']);

        return response()->json($technicians);
    }

    /**
     * CS/Pimpinan membuat tugas baru
     */
    public function createJob(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'technician_id' => 'required|exists:users,id',
        ]);

        $job = Job::create([
            'title'         => $request->title,
            'description'   => $request->description,
            'cs_id'         => auth()->id(),
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
     * Menambah komentar diskusi
     */
    public function addComment(Request $request, $jobId)
    {
        $request->validate(['comment' => 'required|string|max:1000']);

        $comment = JobComment::create([
            'job_id'  => $jobId,
            'user_id' => auth()->id(),
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'comment' => [
                'id'         => $comment->id,
                'comment'    => $comment->comment,
                'user_name'  => auth()->user()->name,
                'user_id'    => auth()->id(),
                'created_at' => $comment->created_at->format('d M Y H:i'),
            ],
        ], 201);
    }

    // --- Helper Formatting ---

    private function formatJobs($jobs): array
    {
        return $jobs->map(fn($j) => $this->formatJob($j))->values()->toArray();
    }

    private function formatJob(Job $job): array
    {
        $trackers = ($job->trackers ?? collect())->map(fn($t) => [
            'id'                => $t->id,
            'step_number'       => $t->step_number,
            'description_value' => $t->description_value,
            'photo_url'         => $t->photo_path ? asset($t->photo_path) : null,
            'video_url'         => $t->video_path ? asset($t->video_path) : null,
            'created_at'        => $t->created_at?->format('d M Y H:i'),
        ])->values()->toArray();

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
            'cs'           => $job->cs ? ['id' => $job->cs->id, 'name' => $job->cs->name] : null,
            'technician'   => $job->technician ? ['id' => $job->technician->id, 'name' => $job->technician->name] : null,
            'trackers'     => $trackers,
            'comments'     => $comments,
            'is_completed' => $job->status === 'completed',
            'is_process'   => $job->status === 'process',
            'is_pending'   => $job->status === 'pending',
            'created_at'   => $job->created_at?->format('d M Y'),
        ];
    }
}