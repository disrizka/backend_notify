<x-app-layout>
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
* { font-family: 'Plus Jakarta Sans', sans-serif; }

.history-bg { background: #f4f6fb; min-height: 100vh; }

.page-header {
    background: linear-gradient(135deg, #1a237e 0%, #1565c0 50%, #0288d1 100%);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.page-header::after {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 200px; height: 200px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}

.job-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(21,101,192,0.08);
    margin-bottom: 24px;
    overflow: hidden;
    border: 1px solid rgba(21,101,192,0.06);
}

.job-card-header {
    padding: 20px 24px;
    background: linear-gradient(to right, #fafbff, #f0f4ff);
    border-bottom: 1px solid #eaeff8;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 12px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.status-completed { background: #e8f5e9; color: #2e7d32; }
.status-process   { background: #e3f2fd; color: #1565c0; }
.status-pending   { background: #fff8e1; color: #e65100; }

.tracker-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
    padding: 20px 24px;
}

.tracker-card {
    background: #f8faff;
    border-radius: 16px;
    border: 1px solid #e3e8f4;
    padding: 16px;
    transition: box-shadow 0.2s;
}
.tracker-card:hover { box-shadow: 0 4px 16px rgba(21,101,192,0.1); }

.step-badge {
    background: linear-gradient(135deg, #1565c0, #1976d2);
    color: white;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    display: inline-block;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.tracker-img {
    width: 100%;
    height: 140px;
    object-fit: cover;
    border-radius: 10px;
    margin-top: 10px;
    border: 1px solid #e0e7f0;
}

.feedback-box {
    margin: 0 24px 20px;
    padding: 18px 20px;
    background: linear-gradient(135deg, #1565c0, #1976d2);
    border-radius: 16px;
    color: white;
}

/* ── Komentar ── */
.comment-section {
    margin: 0 24px 24px;
    border-top: 1px solid #eaeff8;
    padding-top: 20px;
}

.comment-section-title {
    font-size: 14px;
    font-weight: 700;
    color: #0d1b3e;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.comment-list { display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px; }

.comment-item {
    display: flex;
    gap: 10px;
    align-items: flex-start;
}

.comment-avatar {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: linear-gradient(135deg, #1565c0, #42a5f5);
    color: white;
    font-size: 13px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.comment-avatar.kepala { background: linear-gradient(135deg, #6a1b9a, #ab47bc); }
.comment-avatar.cs     { background: linear-gradient(135deg, #00897b, #26a69a); }

.comment-bubble {
    background: #f0f4ff;
    border-radius: 0 14px 14px 14px;
    padding: 10px 14px;
    flex: 1;
}
.comment-meta {
    font-size: 11px;
    color: #8a99b5;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.comment-meta .name { font-weight: 700; color: #1565c0; }
.comment-text { font-size: 13px; color: #2c3e6b; line-height: 1.5; }

.comment-form {
    display: flex;
    gap: 10px;
    align-items: flex-end;
    background: #f8faff;
    border-radius: 14px;
    padding: 14px;
    border: 1px solid #e3e8f4;
}

.comment-input {
    flex: 1;
    border: 1.5px solid #e3e8f4;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 13px;
    resize: none;
    outline: none;
    transition: border-color 0.2s;
    background: white;
    min-height: 44px;
    max-height: 120px;
    font-family: inherit;
}
.comment-input:focus { border-color: #1565c0; }

.btn-send {
    background: linear-gradient(135deg, #1565c0, #1976d2);
    color: white;
    border: none;
    border-radius: 10px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.1s;
    white-space: nowrap;
}
.btn-send:hover { opacity: 0.9; transform: translateY(-1px); }

.btn-delete-comment {
    background: none;
    border: none;
    color: #e53935;
    font-size: 11px;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 6px;
    transition: background 0.2s;
}
.btn-delete-comment:hover { background: #ffebee; }

.no-comments {
    text-align: center;
    padding: 16px;
    color: #b0bcd4;
    font-size: 13px;
}

@media (max-width: 640px) {
    .tracker-grid { grid-template-columns: 1fr 1fr; }
    .job-card-header { flex-direction: column; }
    .comment-form { flex-direction: column; }
    .btn-send { width: 100%; }
}
</style>

<div class="history-bg p-4 md:p-8">

    {{-- Header --}}
    <div class="page-header">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p style="color:rgba(255,255,255,0.65);font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">
                    TRACKER TUGAS
                </p>
                <h1 style="color:white;font-size:24px;font-weight:800;margin:0;">
                    Riwayat Pekerjaan
                </h1>
                <p style="color:rgba(255,255,255,0.65);font-size:13px;margin:4px 0 0;">
                    Total: <strong style="color:white;">{{ $jobs->count() }} tugas</strong>
                </p>
            </div>
            @if(Auth::user()->role === 'kepala' || (Auth::user()->division && Auth::user()->division->name === 'Customer Service'))
            <a href="{{ route('jobs.create') }}"
               style="background:rgba(255,255,255,0.15);color:white;border:1px solid rgba(255,255,255,0.3);padding:10px 20px;border-radius:12px;font-size:13px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Buat Tugas Baru
            </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm font-semibold">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @forelse($jobs as $job)
    <div class="job-card">

        {{-- Header Kartu --}}
        <div class="job-card-header">
            <div style="flex:1;min-width:0;">
                <h3 style="font-size:17px;font-weight:800;color:#0d1b3e;margin:0 0 6px;">{{ $job->title }}</h3>
                <div style="display:flex;flex-wrap:wrap;gap:12px;font-size:12px;color:#8a99b5;">
                    <span>
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:-1px;margin-right:3px;">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                        Teknisi: <strong style="color:#1565c0;">{{ $job->technician->name ?? '-' }}</strong>
                    </span>
                    <span>
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:-1px;margin-right:3px;">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                        CS: <strong style="color:#00897b;">{{ $job->cs->name ?? '-' }}</strong>
                    </span>
                    @if($job->created_at)
                    <span>{{ $job->created_at->format('d M Y') }}</span>
                    @endif
                </div>
            </div>
            <span class="status-badge {{ $job->status === 'completed' ? 'status-completed' : ($job->status === 'process' ? 'status-process' : 'status-pending') }}">
                {{ $job->status === 'completed' ? '✓ Selesai' : ($job->status === 'process' ? '⟳ Berlangsung' : '○ Menunggu') }}
            </span>
        </div>

        {{-- Tracker Grid --}}
        @if($job->trackers->count() > 0)
        <div class="tracker-grid">
            @foreach($job->trackers as $tracker)
            <div class="tracker-card">
                <span class="step-badge">Tahap {{ $tracker->step_number }}</span>
                @if($tracker->description_value)
                <p style="font-size:12px;color:#546e7a;margin:0;line-height:1.5;">{{ $tracker->description_value }}</p>
                @endif
                @if($tracker->photo_path)
                    <img src="{{ asset($tracker->photo_path) }}" 
                        class="tracker-img" 
                        alt="Foto tahap {{ $tracker->step_number }}"
                        onerror="this.src='https://placehold.co/400x300?text=Foto+Tidak+Ditemukan'">
                @endif

                @if($tracker->video_path)
                    <video controls class="tracker-img" style="height:120px;background:#000;">
                        {{-- Hapus 'storage/' di sini juga --}}
                        <source src="{{ asset($tracker->video_path) }}" type="video/mp4">
                    </video>
                @endif
                @if($tracker->created_at)
                <p style="font-size:10px;color:#b0bcd4;margin:8px 0 0;">{{ $tracker->created_at->format('d M Y H:i') }}</p>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        {{-- Feedback Pimpinan --}}
        @if(Auth::user()->role === 'kepala')
        <div style="margin: 0 24px 16px;">
            <form action="{{ route('jobs.feedback', $job->id) }}" method="POST">
                @csrf
                <div style="background:#f0f4ff;border-radius:14px;padding:16px;border:1px solid #e3e8f4;">
                    <label style="font-size:11px;font-weight:700;color:#1565c0;text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:8px;">
                        Update Feedback / Instruksi
                    </label>
                    <textarea name="feedback" rows="2"
                        style="width:100%;border:1.5px solid #e3e8f4;border-radius:10px;padding:10px 12px;font-size:13px;resize:none;outline:none;font-family:inherit;"
                        placeholder="Tulis feedback atau instruksi untuk teknisi...">{{ old('feedback', $job->feedback) }}</textarea>
                    <div style="display:flex;justify-content:flex-end;margin-top:8px;">
                        <button type="submit"
                            style="background:linear-gradient(135deg,#1565c0,#1976d2);color:white;border:none;border-radius:10px;padding:8px 20px;font-size:12px;font-weight:700;cursor:pointer;">
                            Simpan Feedback
                        </button>
                    </div>
                </div>
            </form>
        </div>
        @endif

        @if($job->feedback)
        <div class="feedback-box">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                <svg width="16" height="16" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                <span style="font-size:11px;font-weight:700;color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:0.5px;">
                    Feedback Pimpinan
                </span>
            </div>
            <p style="font-size:14px;color:white;margin:0;line-height:1.5;font-style:italic;">
                "{{ $job->feedback }}"
            </p>
        </div>
        @endif

        {{-- ── SEKSI KOMENTAR ── --}}
        <div class="comment-section">
            <div class="comment-section-title">
                <svg width="16" height="16" fill="none" stroke="#1565c0" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                Komentar
                @if($job->comments->count() > 0)
                <span style="background:#1565c0;color:white;font-size:10px;padding:2px 8px;border-radius:20px;">
                    {{ $job->comments->count() }}
                </span>
                @endif
            </div>

            {{-- List komentar --}}
            @if($job->comments->count() > 0)
            <div class="comment-list">
                @foreach($job->comments as $comment)
                @php
                    $initials = collect(explode(' ', $comment->user->name))->take(2)->map(fn($w) => strtoupper($w[0]))->join('');
                    $role = $comment->user->role ?? 'karyawan';
                    $avatarClass = $role === 'kepala' ? 'kepala' : ($comment->user->division && $comment->user->division->name === 'Customer Service' ? 'cs' : '');
                @endphp
                <div class="comment-item">
                    <div class="comment-avatar {{ $avatarClass }}">{{ $initials }}</div>
                    <div class="comment-bubble">
                        <div class="comment-meta">
                            <span class="name">{{ $comment->user->name }}</span>
                            <span>·</span>
                            <span>{{ $comment->created_at->diffForHumans() }}</span>
                            @if(Auth::user()->role === 'kepala' || $comment->user_id === Auth::id())
                            <form action="{{ route('jobs.comment.destroy', $comment->id) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-delete-comment"
                                    onclick="return confirm('Hapus komentar ini?')">Hapus</button>
                            </form>
                            @endif
                        </div>
                        <p class="comment-text">{{ $comment->comment }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="no-comments">
                <svg width="32" height="32" fill="none" stroke="#ccc" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 6px;">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                <p>Belum ada komentar. Jadilah yang pertama!</p>
            </div>
            @endif

            {{-- Form komentar --}}
            <form action="{{ route('jobs.comment', $job->id) }}" method="POST">
                @csrf
                <div class="comment-form">
                    @php
                        $myInitials = collect(explode(' ', Auth::user()->name))->take(2)->map(fn($w) => strtoupper($w[0]))->join('');
                        $myRole = Auth::user()->role;
                        $myAvatarClass = $myRole === 'kepala' ? 'kepala' : (Auth::user()->division && Auth::user()->division->name === 'Customer Service' ? 'cs' : '');
                    @endphp
                    <div class="comment-avatar {{ $myAvatarClass }}" style="flex-shrink:0;">{{ $myInitials }}</div>
                    <textarea
                        name="comment"
                        class="comment-input"
                        placeholder="Tulis komentar atau catatan pekerjaan..."
                        rows="1"
                        required
                        onInput="this.style.height='auto';this.style.height=this.scrollHeight+'px'"
                    ></textarea>
                    <button type="submit" class="btn-send">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display:inline;vertical-align:-2px;margin-right:4px;">
                            <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                        </svg>
                        Kirim
                    </button>
                </div>
            </form>
        </div>

    </div>
    @empty
    <div style="background:white;border-radius:20px;padding:60px 32px;text-align:center;box-shadow:0 4px 20px rgba(21,101,192,0.08);">
        <svg width="64" height="64" fill="none" stroke="#ccc" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 16px;">
            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p style="font-size:16px;font-weight:700;color:#b0bcd4;margin:0 0 6px;">Belum ada riwayat tugas</p>
        <p style="font-size:13px;color:#ccc;margin:0;">Tugas yang sudah dikerjakan akan muncul di sini.</p>
    </div>
    @endforelse

</div>
</x-app-layout>