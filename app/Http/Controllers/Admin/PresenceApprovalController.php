<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use Illuminate\Http\Request;
use App\Models\OfficeSetting;
use App\Models\Leave;
use Illuminate\Support\Facades\DB;

class PresenceApprovalController extends Controller
{
    /**
     * 1. Halaman Utama Approval Absensi (Kategori 'masuk')
     */
    public function index(Request $request)
    {
        $status = $request->query('status'); 
        
        $query = Presence::query()
            ->with(['user'])
            ->where('category', 'masuk'); 

        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('is_approved', $status);
        }

        $presences = $query->orderBy('date', 'desc')->get();
        return view('admin.attendance.approval', compact('presences', 'status'));
    }

    /**
     * 2. Perizinan (Sakit/Cuti)
     */
    public function perizinan()
    {
        $permissions = Leave::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.attendance.perizinan', compact('permissions'));
    }

    public function leaveApprove($id)
    {
        Leave::findOrFail($id)->update(['status' => 'approved']);
        return back()->with('success', 'Pengajuan izin berhasil disetujui.');
    }

    public function leaveReject($id)
    {
        Leave::findOrFail($id)->update(['status' => 'rejected']);
        return back()->with('success', 'Pengajuan izin ditolak.');
    }

    public function updateStatus(Request $request, $id, $status)
    {
        if (!in_array($status, ['approved', 'rejected', 'pending'])) {
            return back()->with('error', 'Status tidak valid!');
        }

        $type = $request->input('type', 'in');

        if ($type === 'out') {
            DB::table('presences')->where('id', $id)->update(['is_approved_out' => $status]);
        } else {
            DB::table('presences')->where('id', $id)->update(['is_approved' => $status]);
        }

        return back()->with('success', 'Status berhasil diperbarui!');
    }
/**
 * 3. Sub-menu: Jadwal Kerja
 */
public function schedule()
    {
        $manualHolidays = \DB::table('holidays')->get()->keyBy('holiday_date');
 
        $events    = [];
        $yearStart = now()->startOfYear();
        $yearEnd   = now()->endOfYear();
 
        for ($date = $yearStart->copy(); $date->lte($yearEnd); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
 
            // Jumat → otomatis libur
            if ($date->isFriday()) {
                $title = isset($manualHolidays[$dateStr])
                    ? ($manualHolidays[$dateStr]->name ?? 'Libur Mingguan (Jumat)')
                    : 'Libur Mingguan (Jumat)';
 
                $events[$dateStr] = [
                    'title' => $title,
                    'start' => $dateStr,
                    'color' => '#E53935',
                ];
                continue;
            }
 
            // Hari lain yang ada di tabel manual
            if (isset($manualHolidays[$dateStr])) {
                $events[$dateStr] = [
                    'title' => $manualHolidays[$dateStr]->name ?? 'Libur Kantor',
                    'start' => $dateStr,
                    'color' => '#E53935',
                ];
            }
        }
 
        $holidays = array_values($events);
        return view('admin.presence.schedule', compact('holidays'));
    }
 
    public function toggleHoliday(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $date   = $request->date;
        $carbon = \Carbon\Carbon::parse($date);
 
        // Blokir toggle Jumat
        if ($carbon->isFriday()) {
            return response()->json([
                'success' => false,
                'message' => 'Hari Jumat otomatis libur dan tidak dapat diubah.',
            ]);
        }
 
        $exists = \DB::table('holidays')->where('holiday_date', $date)->first();
 
        if ($exists) {
            \DB::table('holidays')->where('holiday_date', $date)->delete();
            $message = "Tanggal {$date} kembali menjadi HARI KERJA.";
        } else {
            \DB::table('holidays')->insert([
                'holiday_date' => $date,
                'name'         => 'Libur Kantor',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
            $message = "Tanggal {$date} berhasil diset sebagai HARI LIBUR.";
        }
 
        return response()->json(['success' => true, 'message' => $message]);
    }

    /**
     * 4. Riwayat Presensi
     */
    public function history(Request $request)
    {
        $selectedMonth = (int) $request->query('month', now()->month);
        $selectedYear  = (int) $request->query('year',  now()->year);
        $search        = $request->query('search');

        $months = [
            'Januari','Februari','Maret','April','Mei','Juni',
            'Juli','Agustus','September','Oktober','November','Desember'
        ];

        $usersQuery = \App\Models\User::with('division')
            ->where('role', 'karyawan');

        if ($search) {
            $usersQuery->where('name', 'like', "%{$search}%");
        }

        $users = $usersQuery->orderBy('name')->get();

        $allPresences = Presence::whereIn('user_id', $users->pluck('id'))
            ->whereMonth('date', $selectedMonth)
            ->whereYear('date',  $selectedYear)
            ->orderBy('date', 'desc')
            ->get();

        $presenceData = $allPresences->groupBy('user_id');

        $totalApproved = $allPresences->where('is_approved', 'approved')->whereNotNull('check_out')->count();
        $totalPending  = $allPresences->where('is_approved', 'pending')->count();
        $totalRejected = $allPresences->where('is_approved', 'rejected')->count();

        return view('admin.attendance.history', compact(
            'users',
            'presenceData',
            'selectedMonth',
            'selectedYear',
            'months',
            'totalApproved',
            'totalPending',
            'totalRejected'
        ));
    }

    /**
     * 5. Settings Absensi
     */
    public function settings() 
    {
        $setting = OfficeSetting::first() ?? new OfficeSetting();
        return view('admin.attendance.settings', compact('setting'));
    }

    public function updateSettings(Request $request) 
    {
        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'radius' => 'required|numeric',
        ]);

        OfficeSetting::updateOrCreate(
            ['id' => 1], 
            [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'radius' => $request->radius,
            ]
        );

        return back()->with('success', 'Pengaturan berhasil diperbarui!');
    }
}