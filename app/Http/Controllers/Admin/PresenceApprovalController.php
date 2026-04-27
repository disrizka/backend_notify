<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use Illuminate\Http\Request;
use App\Models\OfficeSetting;
use App\Models\Leave;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class PresenceApprovalController extends Controller
{
    public function index(Request $request)
    {
        $presences = Presence::with('user')
            ->where(function ($q) {
                $q->where('is_approved', 'pending')
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('check_out')
                         ->where(function ($q3) {
                             $q3->where('is_approved_out', 'pending')
                                ->orWhereNull('is_approved_out');
                         });
                  });
            })
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();
 
        return view('admin.attendance.approval', compact('presences'));
    }

    public function perizinan(Request $request)
    {
        $permissions = Leave::with('user')
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')") 
            ->orderByDesc('created_at')
            ->get();

        return view('admin.attendance.perizinan', compact('permissions'));
    }
 
    public function leaveApprove(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);
        $leave->update(['status' => 'approved']);
        return back()->with('success', 'Perizinan disetujui.');
    }
 
    public function leaveReject(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);
        $leave->update(['status' => 'rejected']);
        return back()->with('success', 'Perizinan ditolak.');
    }
public function schedule()
    {
        $manualHolidays = \DB::table('holidays')->get()->keyBy('holiday_date');
 
        $events    = [];
        $yearStart = now()->startOfYear();
        $yearEnd   = now()->endOfYear();
        for ($date = $yearStart->copy(); $date->lte($yearEnd); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
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

     public function updateStatus(Request $request, $id, $status)
    {
        $presence = Presence::findOrFail($id);
        $type     = $request->query('type') ?? $request->input('type', 'in');
 
        if ($type === 'out') {
            $presence->is_approved_out = $status; 
        } else {
            $presence->is_approved     = $status;
        }
 
        $presence->save();
 
        $label = $status === 'approved' ? 'disetujui' : 'ditolak';
        return redirect()
            ->route('admin.presence.index')
            ->with('success', "Absensi berhasil {$label}. Data sudah masuk ke Riwayat.");
    }

   public function history(Request $request)
{
    $selectedMonth = (int) $request->query('month', now()->month);
    $selectedYear  = (int) $request->query('year',  now()->year);
    $search        = $request->query('search');

    $months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

    $usersQuery = User::with('division')->where('role', '!=', 'kepala');
    if ($search) {
        $usersQuery->where('name', 'like', "%{$search}%");
    }
    $users = $usersQuery->orderBy('name')->get();
    $presenceData = Presence::whereMonth('date', $selectedMonth)
        ->whereYear('date', $selectedYear)
        ->get()
        ->groupBy('user_id');
    $leaveData = Leave::where('status', 'approved')
        ->where(function($q) use ($selectedMonth, $selectedYear) {
            $q->whereMonth('start_date', $selectedMonth)->whereYear('start_date', $selectedYear)
              ->orWhereMonth('end_date', $selectedMonth)->whereYear('end_date', $selectedYear);
        })
        ->get()
        ->groupBy('user_id');

    $allPresences = Presence::whereMonth('date', $selectedMonth)->whereYear('date', $selectedYear)->get();
    $totalApproved = $allPresences->where('is_approved', 'approved')->count();
    $totalPending  = $allPresences->where('is_approved', 'pending')->count();
    $totalRejected = $allPresences->where('is_approved', 'rejected')->count();

    return view('admin.attendance.history', compact(
        'users', 'presenceData', 'leaveData', 'selectedMonth', 'selectedYear', 
        'months', 'totalApproved', 'totalPending', 'totalRejected'
    ));
}

    public function settings() 
    {
        $setting = OfficeSetting::first() ?? new OfficeSetting();
        return view('admin.attendance.settings', compact('setting'));
    }

    public function updateSettings(Request $request) 
{
    dd($request->all());
    $request->validate([
        'latitude'       => 'required',
        'longitude'      => 'required',
        'radius'         => 'required|numeric',
        'check_in_time'  => 'required',
        'check_out_time' => 'required',
        'late_tolerance' => 'required|numeric',
    ]);

    OfficeSetting::updateOrCreate(
        ['id' => 1], 
        [
            'latitude'        => $request->latitude,
            'longitude'       => $request->longitude,
            'radius'          => $request->radius,
            'radius_enforced' => $request->has('radius_enforced') ? 1 : 0,
            'check_in_time'   => $request->check_in_time,
            'check_out_time'  => $request->check_out_time,
            'late_tolerance'  => $request->late_tolerance,
        ]
    );

    return back()->with('success', 'Pengaturan berhasil diperbarui!');
}
}