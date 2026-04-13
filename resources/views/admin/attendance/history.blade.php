<x-app-layout>
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap');

* { font-family: 'Plus Jakarta Sans', sans-serif; }
.mono { font-family: 'JetBrains Mono', monospace; }

.attendance-bg {
    background: linear-gradient(135deg, #ffffff 0%, #ffffff 50%, #ffffff 100%);
    min-height: 100vh;
}

.page-hero {
    background: linear-gradient(135deg, #1a237e 0%, #1565c0 45%, #0288d1 100%);
    border-radius: 24px;
    padding: 32px 36px;
    position: relative;
    overflow: hidden;
    margin-bottom: 28px;
}
.page-hero::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 240px; height: 240px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}
.page-hero::after {
    content: '';
    position: absolute;
    bottom: -40px; left: 30%;
    width: 160px; height: 160px;
    background: rgba(255,255,255,0.04);
    border-radius: 50%;
}

.filter-bar {
    background: white;
    border-radius: 18px;
    padding: 18px 22px;
    box-shadow: 0 2px 12px rgba(21,101,192,0.07);
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}
.filter-bar select, .filter-bar input {
    border: 1.5px solid #e3e8f4;
    border-radius: 12px;
    padding: 9px 14px;
    font-size: 13px;
    color: #1a237e;
    background: #f8faff;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-weight: 600;
    outline: none;
    transition: border-color 0.2s;
}
.filter-bar select:focus, .filter-bar input:focus { border-color: #1565c0; }
.filter-btn {
    background: linear-gradient(135deg, #1565c0, #1976d2);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 9px 20px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.2s;
    font-family: 'Plus Jakarta Sans', sans-serif;
}
.filter-btn:hover { opacity: 0.9; transform: translateY(-1px); }

.emp-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 3px 16px rgba(21,101,192,0.07);
    border: 1px solid rgba(21,101,192,0.06);
    overflow: hidden;
    margin-bottom: 16px;
    transition: box-shadow 0.2s;
}
.emp-card:hover { box-shadow: 0 6px 24px rgba(21,101,192,0.12); }

.emp-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 22px;
    cursor: pointer;
    user-select: none;
    background: linear-gradient(to right, #fafbff, #f0f4ff);
    border-bottom: 1px solid #eaeff8;
    transition: background 0.2s;
}
.emp-header:hover { background: linear-gradient(to right, #f0f4ff, #e8eeff); }

.emp-avatar {
    width: 44px; height: 44px;
    border-radius: 14px;
    background: linear-gradient(135deg, #1565c0, #42a5f5);
    display: flex; align-items: center; justify-content: center;
    color: white;
    font-size: 17px;
    font-weight: 800;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(21,101,192,0.25);
}

.emp-badges { display: flex; gap: 8px; flex-wrap: wrap; }
.badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 11px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}
.badge-green  { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
.badge-amber  { background: #fff8e1; color: #e65100; border: 1px solid #ffe0b2; }
.badge-red    { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
.badge-blue   { background: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; }
.badge-gray   { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }

.toggle-icon {
    width: 32px; height: 32px;
    border-radius: 10px;
    background: rgba(21,101,192,0.08);
    display: flex; align-items: center; justify-content: center;
    transition: transform 0.3s, background 0.2s;
    flex-shrink: 0;
}
.toggle-icon.open { transform: rotate(180deg); background: rgba(21,101,192,0.15); }

.emp-body {
    display: none;
    padding: 0 22px 18px;
    animation: slideDown 0.25s ease;
}
.emp-body.open { display: block; }

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}

.presence-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 14px;
}
.presence-table th {
    background: #ffffff;
    color: #1565c0;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    padding: 10px 12px;
    text-align: left;
    white-space: nowrap;
}
.presence-table th:first-child { border-radius: 10px 0 0 10px; }
.presence-table th:last-child  { border-radius: 0 10px 10px 0; }
.presence-table td {
    padding: 10px 12px;
    font-size: 13px;
    color: #2c3e6b;
    border-bottom: 1px solid #f0f4ff;
    vertical-align: middle;
}
.presence-table tr:last-child td { border-bottom: none; }
.presence-table tr:hover td { background: #fafbff; }

.time-chip {
    display: inline-flex; align-items: center; gap: 5px;
    background: #f0f4ff;
    border-radius: 8px;
    padding: 4px 10px;
    font-size: 13px;
    font-weight: 700;
    color: #1565c0;
}
.time-chip.out { background: #e8f5e9; color: #2e7d32; }
.time-chip.empty { background: #f5f5f5; color: #9e9e9e; }

.duration-pill {
    display: inline-block;
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    color: #1565c0;
    border-radius: 20px;
    padding: 3px 10px;
    font-size: 11px;
    font-weight: 700;
}

.status-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 5px;
}

/* ── Photo thumbnail ── */
.photo-thumb {
    width: 38px; height: 38px;
    object-fit: cover;
    border-radius: 8px;
    border: 1.5px solid #e3e8f4;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
    display: block;
}
.photo-thumb:hover {
    transform: scale(1.08);
    box-shadow: 0 4px 14px rgba(21,101,192,0.2);
    border-color: #1565c0;
}
.photo-empty {
    width: 38px; height: 38px;
    background: #f3f4f6;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
}

/* Lightbox */
#photo-lightbox {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.85);
    z-index: 9999;
    align-items: center; justify-content: center;
    flex-direction: column;
    gap: 16px;
    backdrop-filter: blur(4px);
}
#photo-lightbox.active { display: flex; }
#photo-lightbox img {
    max-width: 90vw;
    max-height: 80vh;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}
#photo-lightbox button {
    position: absolute; top: 20px; right: 24px;
    background: rgba(255,255,255,0.15);
    border: none; color: white; font-size: 26px;
    width: 44px; height: 44px;
    border-radius: 50%;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background 0.2s;
}
#photo-lightbox button:hover { background: rgba(255,255,255,0.25); }
#lightbox-caption {
    color: rgba(255,255,255,0.7);
    font-size: 13px;
    font-weight: 600;
}

.empty-row td {
    text-align: center;
    padding: 32px;
    color: #9e9e9e;
    font-size: 13px;
}

@media (max-width: 640px) {
    .page-hero { padding: 22px 20px; }
    .filter-bar { flex-direction: column; align-items: stretch; }
    .filter-bar select, .filter-bar input, .filter-btn { width: 100%; }
    .presence-table { font-size: 12px; }
    .presence-table th, .presence-table td { padding: 8px 9px; }
}
</style>

{{-- Lightbox overlay --}}
<div id="photo-lightbox" onclick="closeLightbox(event)">
    <button onclick="closeLightbox()">×</button>
    <img id="lightbox-img" src="" alt="Foto Absensi">
    <span id="lightbox-caption"></span>
</div>

<div class="attendance-bg p-4 md:p-8">

    {{-- ── HERO HEADER ── --}}
    <div class="page-hero">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div style="width:42px;height:42px;background:rgba(255,255,255,0.15);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                        <svg width="22" height="22" fill="none" stroke="white" stroke-width="2.2" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="3"/><path d="M16 2v4M8 2v4M3 10h18"/>
                            <path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/>
                        </svg>
                    </div>
                    <span style="color:rgba(255,255,255,0.7);font-size:13px;font-weight:600;letter-spacing:0.5px;">KEHADIRAN</span>
                </div>
                <h1 style="color:white;font-size:26px;font-weight:800;margin:0 0 4px;">Riwayat Presensi Karyawan</h1>
                <p style="color:rgba(255,255,255,0.65);font-size:13px;margin:0;">
                    Periode: <strong style="color:rgba(255,255,255,0.9);">{{ $months[$selectedMonth - 1] }} {{ $selectedYear }}</strong>
                    &nbsp;·&nbsp; Total: <strong style="color:rgba(255,255,255,0.9);">{{ $users->count() }} karyawan</strong>
                </p>
            </div>
            <div class="flex gap-3 flex-wrap">
                <div class="stat-mini">
                    <div style="color:rgba(255,255,255,0.6);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Total Hadir</div>
                    <div style="color:white;font-size:28px;font-weight:800;line-height:1.1;">{{ $totalApproved }}</div>
                </div>
                <div style="width:1px;background:rgba(255,255,255,0.15);"></div>
                <div class="stat-mini">
                    <div style="color:rgba(255,255,255,0.6);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Pending</div>
                    <div style="color:#ffd54f;font-size:28px;font-weight:800;line-height:1.1;">{{ $totalPending }}</div>
                </div>
                <div style="width:1px;background:rgba(255,255,255,0.15);"></div>
                <div class="stat-mini">
                    <div style="color:rgba(255,255,255,0.6);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Ditolak</div>
                    <div style="color:#ef9a9a;font-size:28px;font-weight:800;line-height:1.1;">{{ $totalRejected }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── FILTER BAR ── --}}
    <form method="GET" action="{{ route('admin.presence.history') }}" class="filter-bar">
        <svg width="18" height="18" fill="none" stroke="#1565c0" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;">
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
        </svg>
        <span style="font-size:13px;font-weight:700;color:#1565c0;white-space:nowrap;">Filter:</span>

        <select name="month" style="min-width:130px;">
            @foreach($months as $i => $name)
                <option value="{{ $i+1 }}" {{ $selectedMonth == $i+1 ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>

        <select name="year" style="min-width:100px;">
            @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>

        <div style="flex:1;min-width:160px;position:relative;">
            <svg width="14" height="14" fill="none" stroke="#8a99b5" stroke-width="2" viewBox="0 0 24 24"
                style="position:absolute;left:12px;top:50%;transform:translateY(-50%);">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari nama karyawan..."
                style="width:100%;padding-left:34px;">
        </div>

        <button type="submit" class="filter-btn">Tampilkan</button>

        @if(request('search') || request('month') || request('year'))
        <a href="{{ route('admin.presence.history') }}"
            style="font-size:12px;color:#8a99b5;font-weight:600;text-decoration:none;white-space:nowrap;">
            Reset
        </a>
        @endif
    </form>

    {{-- ── EMPLOYEE LIST ── --}}
    @forelse($users as $user)
    @php
        $records  = $presenceData[$user->id] ?? collect();

        $approved = $records->where('is_approved', 'approved')->whereNotNull('check_out')->count();
        $belumOut = $records->where('is_approved', 'approved')->whereNull('check_out')->count();
        $pending  = $records->where('is_approved', 'pending')->count();
        $rejected = $records->where('is_approved', 'rejected')->count();

        $initials = collect(explode(' ', $user->name))->take(2)->map(fn($w) => strtoupper($w[0]))->join('');
    @endphp

    <div class="emp-card" x-data="{ open: false }">

        <div class="emp-header" @click="open = !open">
            <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
                <div class="emp-avatar">{{ $initials }}</div>
                <div style="min-width:0;">
                    <div style="font-size:15px;font-weight:800;color:#0d1b3e;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $user->name }}
                    </div>
                    <div style="font-size:12px;color:#8a99b5;margin-top:2px;">
                        {{ $user->division->name ?? 'Tanpa Divisi' }}
                        &nbsp;·&nbsp;
                        <span style="font-weight:600;color:#1565c0;">{{ $records->count() }} hari</span>
                    </div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <div class="emp-badges">
                    @if($approved > 0)
                    <span class="badge badge-green">
                        <svg width="9" height="9" fill="#2e7d32" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>
                        {{ $approved }} Hadir
                    </span>
                    @endif
                    @if($belumOut > 0)
                    <span class="badge badge-blue">
                        <svg width="9" height="9" fill="#1565c0" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>
                        {{ $belumOut }} Belum Check Out
                    </span>
                    @endif
                    @if($pending > 0)
                    <span class="badge badge-amber">
                        <svg width="9" height="9" fill="#e65100" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>
                        {{ $pending }} Pending
                    </span>
                    @endif
                    @if($rejected > 0)
                    <span class="badge badge-red">
                        <svg width="9" height="9" fill="#c62828" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>
                        {{ $rejected }} Ditolak
                    </span>
                    @endif
                    @if($records->count() == 0)
                    <span class="badge" style="background:#f5f5f5;color:#9e9e9e;border:1px solid #e0e0e0;">Tidak ada data</span>
                    @endif
                </div>
                <div class="toggle-icon" :class="{ open: open }">
                    <svg width="14" height="14" fill="none" stroke="#1565c0" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M6 9l6 6 6-6"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Presence table --}}
        <div class="emp-body" :class="{ open: open }" x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">

            <div style="overflow-x:auto;">
                <table class="presence-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Hari</th>
                            <th>Masuk</th>
                            <th>Foto IN</th>
                            <th>Pulang</th>
                            <th>Foto OUT</th>
                            <th>Durasi</th>
                            <th>Ket. IN</th>
                            <th>Ket. OUT</th>
                            <th>Status IN</th>
                            <th>Status OUT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $rec)
                        @php
                            $dt = \Carbon\Carbon::parse($rec->date);
                            $days = ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
                            $dayName = $days[$dt->dayOfWeek == 0 ? 6 : $dt->dayOfWeek - 1];

                            $duration = '-';
                            if ($rec->check_in && $rec->check_out) {
                                $in  = \Carbon\Carbon::createFromTimeString($rec->check_in);
                                $out = \Carbon\Carbon::createFromTimeString($rec->check_out);
                                $diff = $in->diffInMinutes($out);
                                $duration = ($diff >= 60 ? floor($diff/60).'j ' : '') . ($diff%60) . 'm';
                            }

                            $statusIn = match($rec->is_approved) {
                                'approved' => ['dot'=>'#4caf50','bg'=>'#e8f5e9','text'=>'#2e7d32','label'=>'Disetujui'],
                                'rejected' => ['dot'=>'#f44336','bg'=>'#ffebee','text'=>'#c62828','label'=>'Ditolak'],
                                default    => ['dot'=>'#ff9800','bg'=>'#fff8e1','text'=>'#e65100','label'=>'Pending'],
                            };

                            $approvedOut = $rec->is_approved_out ?? 'pending';
                            $statusOut = $rec->check_out ? match($approvedOut) {
                                'approved' => ['dot'=>'#4caf50','bg'=>'#e8f5e9','text'=>'#2e7d32','label'=>'Disetujui'],
                                'rejected' => ['dot'=>'#f44336','bg'=>'#ffebee','text'=>'#c62828','label'=>'Ditolak'],
                                default    => ['dot'=>'#ff9800','bg'=>'#fff8e1','text'=>'#e65100','label'=>'Pending'],
                            } : null;
                        @endphp
                        <tr>
                            {{-- Tanggal --}}
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div style="width:32px;height:32px;background:#f0f4ff;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#1565c0;" class="mono">
                                        {{ $dt->day }}
                                    </div>
                                    <span style="font-size:12px;color:#8a99b5;">{{ $dt->format('M Y') }}</span>
                                </div>
                            </td>
                            {{-- Hari --}}
                            <td>
                                <span style="font-size:12px;font-weight:600;color:#546e7a;">{{ $dayName }}</span>
                            </td>
                            {{-- Jam Masuk --}}
                            <td>
                                @if($rec->check_in)
                                <span class="time-chip mono">
                                    <svg width="11" height="11" fill="none" stroke="#1565c0" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    {{ substr($rec->check_in, 0, 5) }}
                                </span>
                                @else
                                <span class="time-chip empty mono">--:--</span>
                                @endif
                            </td>
                            {{-- ── FOTO IN ── --}}
                            <td>
                                @if($rec->photo_in)
                                    <img src="{{ asset($rec->photo_in) }}"
                                         class="photo-thumb"
                                         onclick="openLightbox('{{ asset($rec->photo_in) }}', 'Foto Masuk — {{ $user->name }} ({{ substr($rec->check_in ?? '', 0, 5) }})')"
                                         title="Klik untuk perbesar">
                                @else
                                    <div class="photo-empty">
                                        <svg width="16" height="16" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                            <circle cx="12" cy="13" r="4"/>
                                        </svg>
                                    </div>
                                @endif
                            </td>
                            {{-- Jam Pulang --}}
                            <td>
                                @if($rec->check_out)
                                <span class="time-chip out mono">
                                    <svg width="11" height="11" fill="none" stroke="#2e7d32" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    {{ substr($rec->check_out, 0, 5) }}
                                </span>
                                @else
                                <span class="time-chip empty mono">--:--</span>
                                @endif
                            </td>
                            {{-- ── FOTO OUT ── --}}
                            <td>
                                @if($rec->photo_out)
                                    <img src="{{ asset($rec->photo_out) }}"
                                         class="photo-thumb"
                                         onclick="openLightbox('{{ asset($rec->photo_out) }}', 'Foto Pulang — {{ $user->name }} ({{ substr($rec->check_out ?? '', 0, 5) }})')"
                                         title="Klik untuk perbesar">
                                @else
                                    <div class="photo-empty">
                                        <svg width="16" height="16" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                            <circle cx="12" cy="13" r="4"/>
                                        </svg>
                                    </div>
                                @endif
                            </td>
                            {{-- Durasi --}}
                            <td>
                                @if($duration !== '-')
                                <span class="duration-pill mono">{{ $duration }}</span>
                                @else
                                <span style="color:#ccc;font-size:12px;">—</span>
                                @endif
                            </td>
                            {{-- Keterangan IN --}}
                            <td style="max-width:130px;">
                                <span style="font-size:12px;color:#546e7a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;max-width:120px;" title="{{ $rec->notes }}">
                                    {{ $rec->notes ?? '-' }}
                                </span>
                            </td>
                            {{-- Keterangan OUT --}}
                            <td style="max-width:130px;">
                                @if($rec->check_out)
                                <span style="font-size:12px;color:#546e7a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;max-width:120px;" title="{{ $rec->notes_out }}">
                                    {{ $rec->notes_out ?? '-' }}
                                </span>
                                @else
                                <span style="color:#ccc;font-size:12px;">—</span>
                                @endif
                            </td>
                            {{-- Status IN --}}
                            <td>
                                <span style="display:inline-flex;align-items:center;gap:5px;
                                    background:{{ $statusIn['bg'] }};color:{{ $statusIn['text'] }};
                                    border-radius:20px;padding:4px 11px;font-size:11px;font-weight:700;white-space:nowrap;">
                                    <span class="status-dot" style="background:{{ $statusIn['dot'] }};"></span>
                                    {{ $statusIn['label'] }}
                                </span>
                            </td>
                            {{-- Status OUT --}}
                            <td>
                                @if(!$rec->check_out)
                                    <span style="display:inline-flex;align-items:center;gap:5px;
                                        background:#f3f4f6;color:#6b7280;
                                        border-radius:20px;padding:4px 11px;font-size:11px;font-weight:700;white-space:nowrap;">
                                        <span class="status-dot" style="background:#9ca3af;"></span>
                                        Belum Check Out
                                    </span>
                                @else
                                    <span style="display:inline-flex;align-items:center;gap:5px;
                                        background:{{ $statusOut['bg'] }};color:{{ $statusOut['text'] }};
                                        border-radius:20px;padding:4px 11px;font-size:11px;font-weight:700;white-space:nowrap;">
                                        <span class="status-dot" style="background:{{ $statusOut['dot'] }};"></span>
                                        {{ $statusOut['label'] }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr class="empty-row">
                            <td colspan="11">
                                <div style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:24px 0;">
                                    <svg width="40" height="40" fill="none" stroke="#ccc" stroke-width="1.5" viewBox="0 0 24 24">
                                        <rect x="3" y="4" width="18" height="18" rx="3"/><path d="M16 2v4M8 2v4M3 10h18"/>
                                        <path d="M8 14h.01M12 14h.01M16 14h.01"/>
                                    </svg>
                                    <span style="color:#bbb;font-size:13px;">Tidak ada data presensi bulan ini</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @empty
    <div style="background:white;border-radius:20px;padding:60px 32px;text-align:center;box-shadow:0 3px 16px rgba(21,101,192,0.07);">
        <svg width="64" height="64" fill="none" stroke="#cccccc" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 16px;">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        <p style="color:#9e9e9e;font-size:16px;font-weight:700;margin:0 0 6px;">Tidak ada karyawan ditemukan</p>
        <p style="color:#bbb;font-size:13px;margin:0;">Coba ubah filter pencarian</p>
    </div>
    @endforelse

</div>

<style>
.stat-mini { text-align:right; }
[x-cloak] { display: none !important; }
</style>

<script>
function openLightbox(src, caption) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox-caption').textContent = caption;
    document.getElementById('photo-lightbox').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeLightbox(e) {
    if (!e || e.target !== document.getElementById('lightbox-img')) {
        document.getElementById('photo-lightbox').classList.remove('active');
        document.body.style.overflow = '';
    }
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLightbox();
});
</script>
</x-app-layout>