<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobTracker;
use App\Models\JobComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    public function create()
    {
        // Kepala & CS bisa buat tugas
        if (Auth::user()->role === 'kepala') {
            $technicians = User::where('role', 'karyawan')->get();
        } else {
            // CS: bisa assign ke semua karyawan NON-CS
            $technicians = User::where('role', 'karyawan')
                ->whereHas('division', function($q) {
                    $q->where('name', '!=', 'Customer Service');
                })->get();
        }

        return view('cs.jobs.create', compact('technicians'));
    }

    public function store(Request $request)
    {
        Job::create([
            'title'          => $request->title,
            'description'    => $request->description,
            'cs_id'          => Auth::id(),
            'technician_id'  => $request->technician_id,
            'status'         => 'pending',
        ]);
        return redirect()->back()->with('success', 'Tugas berhasil dikirim ke Teknisi!');
    }

    public function updateProgress(Request $request, Job $job)
    {
        $request->validate([
            'description' => 'nullable|string',
            'photo'       => 'nullable|image|max:5120',
            'video'       => 'nullable|mimetypes:video/mp4|max:20480',
        ]);

        $photoPath = null;
        $videoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('job_photos', 'public');
        }

        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('job_videos', 'public');
        }

        \App\Models\JobTracker::create([
            'job_id'            => $job->id,
            'step_number'       => $job->current_step,
            'description_value' => $request->description,
            'photo_path'        => $photoPath,
            'video_path'        => $videoPath,
        ]);

        $job->increment('current_step');

        if ($job->current_step > 4) {
            $job->update(['status' => 'completed']);
            return redirect()->route('technician.dashboard')->with('success', 'Tugas Selesai!');
        }

        return back()->with('success', 'Tahap ' . ($job->current_step - 1) . ' berhasil disimpan!');
    }

    public function technicianDashboard()
    {
        $jobs = Job::where('technician_id', Auth::id())
                    ->where('status', '!=', 'completed')
                    ->with('cs')
                    ->get();

        return view('technician.dashboard', compact('jobs'));
    }

    public function acceptJob(Job $job)
    {
        $job->update(['status' => 'process', 'current_step' => 1]);
        return back()->with('success', 'Tugas diambil! Silakan mulai tracker.');
    }

    /**
     * Riwayat tugas - visible untuk semua role
     * - kepala: semua tugas
     * - karyawan: tugas yang dia buat (cs) ATAU dia kerjakan (technician)
     */
    public function history()
{
    // Semua user (Kepala, CS, Teknisi) bisa melihat semua riwayat
    $jobs = Job::with(['cs', 'technician.division', 'trackers', 'comments.user'])
                ->latest()
                ->get();

    return view('jobs.history', compact('jobs'));
}

    public function storeFeedback(Request $request, Job $job)
    {
        $request->validate([
            'feedback' => 'required|string'
        ]);

        $job->update(['feedback' => $request->feedback]);

        return back()->with('success', 'Feedback berhasil disimpan!');
    }

    /**
     * Simpan komentar dari semua karyawan
     */
    public function storeComment(Request $request, Job $job)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        JobComment::create([
            'job_id'  => $job->id,
            'user_id' => Auth::id(),
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'Komentar berhasil ditambahkan!');
    }

    /**
     * Hapus komentar (hanya pemilik atau kepala)
     */
    public function destroyComment(JobComment $comment)
    {
        $user = Auth::user();
        if ($user->role === 'kepala' || $comment->user_id === $user->id) {
            $comment->delete();
            return back()->with('success', 'Komentar dihapus.');
        }
        return back()->with('error', 'Tidak diizinkan.');
    }
}