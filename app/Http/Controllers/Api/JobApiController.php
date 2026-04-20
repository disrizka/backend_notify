<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobTracker;
use App\Models\JobComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\InternalNotification;

class JobApiController extends Controller
{
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

    public function show($id)
    {
        $job = Job::with(['cs', 'technician', 'trackers', 'comments.user'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'job' => $this->formatJob($job)
        ]);
    }

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

    public function acceptJob(Request $request, $jobId)
    {
        $job = Job::findOrFail($jobId);

        if ($job->technician_id !== $request->user()->id) {
            return response()->json(['error' => 'Bukan tugas Anda'], 403);
        }

        $job->update([
            'status'      => 'process',
            'current_step'=> 1,
            'accepted_at' => now(),   // ← catat waktu mulai
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil diambil!',
            'job'     => $this->formatJob($job->load(['cs', 'trackers', 'comments.user'])),
        ]);
    }

    public function updateProgress(Request $request, $id)
    {
        $job  = Job::findOrFail($id);
        $user = auth()->user();

        if ($job->technician_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Hanya teknisi pelaksana yang bisa update'], 403);
        }

        $nextStep = ($job->current_step ?? 0) + 1;

        if ($nextStep > 4) {
            return response()->json(['success' => false, 'message' => 'Tugas sudah selesai'], 400);
        }

        // Foto
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $fileName = time() . '_step' . $nextStep . '.' . $request->file('photo')->getClientOriginalExtension();
            $request->file('photo')->move(public_path('job_photos'), $fileName);
            $photoPath = 'job_photos/' . $fileName;
        }

        // Video
        $videoPath = null;
        if ($request->hasFile('video')) {
            $vName = time() . '_video_step' . $nextStep . '.' . $request->file('video')->getClientOriginalExtension();
            $request->file('video')->move(public_path('job_videos'), $vName);
            $videoPath = 'job_videos/' . $vName;
        }

        JobTracker::create([
            'job_id'            => $job->id,
            'step_number'       => $nextStep,
            'description_value' => $request->description_value,
            'photo_path'        => $photoPath,
            'video_path'        => $videoPath,
        ]);

        $isCompleted = $nextStep >= 4;

        // Hitung durasi aktual jika selesai
        $actualDuration = null;
        if ($isCompleted && $job->accepted_at) {
            $acceptedAt    = \Carbon\Carbon::parse($job->accepted_at);
            $actualDuration = $acceptedAt->diffInMinutes(now()); // menit
        }

        $job->update([
            'current_step'      => $nextStep,
            'status'            => $isCompleted ? 'completed' : 'process',
            'completed_at'      => $isCompleted ? now() : null,
            'actual_duration'   => $actualDuration,
            'completion_reason' => $request->completion_reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Langkah $nextStep berhasil diperbarui",
            'job'     => $this->formatJob($job->load(['trackers', 'cs', 'technician', 'comments.user']))
        ]);
    }

    public function getTechnicians(Request $request)
    {
        $user  = $request->user();
        $query = User::with('division')->where('role', 'karyawan');

        if ($user->role === 'cs' || ($user->division && $user->division->name === 'Customer Service')) {
            $query->whereHas('division', function ($q) {
                $q->where('name', '!=', 'Customer Service');
            });
        }

        $technicians = $query->get()->map(function ($u) {
            return [
                'id'       => $u->id,
                'name'     => $u->name,
                'division' => $u->division ? $u->division->name : '-',
            ];
        });

        return response()->json(['success' => true, 'data' => $technicians]);
    }

    public function createJob(Request $request)
    {
        $request->validate([
            'title'          => 'required|string|max:255',
            'technician_id'  => 'required|exists:users,id',
            'client_name'    => 'nullable|string|max:255',
            'location'       => 'nullable|string',
            'latitude'       => 'nullable|numeric',
            'longitude'      => 'nullable|numeric',
            'start_time'     => 'nullable|date',
            'end_time'       => 'nullable|date',
        ]);

        $job = Job::create([
            'title'          => $request->title,
            'description'    => $request->description,
            'cs_id'          => auth()->id(),
            'technician_id'  => $request->technician_id,
            'status'         => 'pending',
            'client_name'    => $request->client_name,
            'location'       => $request->location,
            'latitude'       => $request->latitude,
            'longitude'      => $request->longitude,
            'start_time'     => $request->start_time,
            'end_time'       => $request->end_time,
        ]);

        $receiver = User::find($request->technician_id);
        if ($receiver) {
            $estInfo = '';
            if ($request->start_time && $request->end_time) {
                $start = \Carbon\Carbon::parse($request->start_time)->format('d M H:i');
                $end   = \Carbon\Carbon::parse($request->end_time)->format('d M H:i');
                $estInfo = " | $start – $end";
            }
            $receiver->notify(new \App\Notifications\InternalNotification([
                'title'   => 'Tugas Baru!',
                'message' => 'Anda mendapatkan tugas: ' . $request->title . $estInfo,
                'type'    => 'job_assigned',
            ]));
        }

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dibuat!',
            'job'     => $job
        ], 201);
    }

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

    // ── Helpers ──────────────────────────────────────────────────────────────

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

        // Hitung status overdue
        $isOverdue = false;
        if ($job->end_time && $job->accepted_at) {
            $deadline  = \Carbon\Carbon::parse($job->end_time);
            $reference = $job->completed_at
                ? \Carbon\Carbon::parse($job->completed_at)
                : now();
            $isOverdue = $reference->isAfter($deadline);
        }

        return [
            'id'                => $job->id,
            'title'             => $job->title,
            'description'       => $job->description,
            'status'            => $job->status,
            'current_step'      => $job->current_step,
            'feedback'          => $job->feedback,
            'cs'                => $job->cs ? ['id' => $job->cs->id, 'name' => $job->cs->name] : null,
            'technician'        => $job->technician
                ? ['id' => $job->technician->id, 'name' => $job->technician->name]
                : null,
            'technician_id'     => $job->technician_id,
            'trackers'          => $trackers,
            'comments'          => $comments,
            'is_completed'      => $job->status === 'completed',
            'is_process'        => $job->status === 'process',
            'is_pending'        => $job->status === 'pending',
            'created_at'        => $job->created_at?->format('d M Y'),

            // ── Fields baru ──
            'client_name'       => $job->client_name,
            'location'          => $job->location,
            'latitude'          => $job->latitude,
            'longitude'         => $job->longitude,
            'start_time'        => $job->start_time?->toIso8601String(),
            'end_time'          => $job->end_time?->toIso8601String(),
            'accepted_at'       => $job->accepted_at?->toIso8601String(),
            'completed_at'      => $job->completed_at?->toIso8601String(),
            'actual_duration'   => $job->actual_duration,   
            'completion_reason' => $job->completion_reason,
            'is_overdue'        => $isOverdue,
        ];
    }
}