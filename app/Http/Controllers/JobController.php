<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobTracker;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    public function create()
    {
        if (Auth::user()->role === 'kepala') {
            $technicians = User::where('role', 'karyawan')->get();
        } else {
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
            'title' => $request->title,
            'description' => $request->description,
            'cs_id' => Auth::id(),
            'technician_id' => $request->technician_id,
            'status' => 'pending',
        ]);
        return redirect()->back()->with('success', 'Tugas berhasil dikirim ke Teknisi!');
    }

    public function updateProgress(Request $request, Job $job)
{
    // Validasi agar teknisi tidak lupa upload jika diwajibkan
    $request->validate([
        'description' => 'nullable|string',
        'photo' => 'nullable|image|max:5120', // Maks 5MB
        'video' => 'nullable|mimetypes:video/mp4|max:20480', // Maks 20MB
    ]);

    $photoPath = null;
    $videoPath = null;

    // Simpan file Foto ke folder public/job_photos
    if ($request->hasFile('photo')) {
        $photoPath = $request->file('photo')->store('job_photos', 'public');
    }

    // Simpan file Video ke folder public/job_videos
    if ($request->hasFile('video')) {
        $videoPath = $request->file('video')->store('job_videos', 'public');
    }

    // Simpan data ke tabel trackers agar muncul di halaman Riwayat
    \App\Models\JobTracker::create([
        'job_id' => $job->id,
        'step_number' => $job->current_step,
        'description_value' => $request->description,
        'photo_path' => $photoPath,
        'video_path' => $videoPath,
    ]);

    // Lanjut ke tahap berikutnya
    $job->increment('current_step');

    // Jika sudah melewati tahap 4, tandai selesai
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
    public function history()
    {
        $jobs = Job::with(['cs', 'technician.division', 'trackers'])
                    ->latest()
                    ->get();

        return view('jobs.history', compact('jobs'));
    }

            public function storeFeedback(Request $request, Job $job)
    {
        $request->validate([
            'feedback' => 'required|string'
        ]);

        $job->update([
            'feedback' => $request->feedback
        ]);

        return back()->with('success', 'Feedback berhasil disimpan!');
    }
}
