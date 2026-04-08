<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index()
    {
        $holidays = array_values($this->buildEvents());
        return view('admin.presence.schedule', compact('holidays'));
    }

    public function toggle(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $date   = $request->date;
        $carbon = Carbon::parse($date);

        if ($carbon->isFriday()) {
            return response()->json([
                'status'  => 'info',
                'message' => 'Hari Jumat otomatis libur dan tidak dapat diubah.',
            ]);
        }

        $exists = Holiday::where('holiday_date', $date)->first();
        if ($exists) {
            $exists->delete();
            $message = "Tanggal {$date} kembali menjadi HARI KERJA.";
        } else {
            Holiday::create(['holiday_date' => $date, 'name' => 'Libur Kantor']);
            $message = "Tanggal {$date} berhasil diset sebagai HARI LIBUR.";
        }

        return response()->json(['status' => 'success', 'message' => $message]);
    }

    private function buildEvents(): array
    {
        $manualHolidays = Holiday::all()->keyBy('holiday_date');
        $events    = [];
        $yearStart = Carbon::now()->startOfYear();
        $yearEnd   = Carbon::now()->endOfYear();

        for ($date = $yearStart->copy(); $date->lte($yearEnd); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');

            if ($date->isFriday()) {
                $title = isset($manualHolidays[$dateStr])
                    ? ($manualHolidays[$dateStr]->name ?? 'Libur Mingguan (Jumat)')
                    : 'Libur Mingguan (Jumat)';

                $events[$dateStr] = [
                    'start'   => $dateStr,
                    'title'   => $title,
                    'display' => 'background',
                    'color'   => '#ef4444',
                ];
                continue;
            }

            if (isset($manualHolidays[$dateStr])) {
                $events[$dateStr] = [
                    'start'   => $dateStr,
                    'title'   => $manualHolidays[$dateStr]->name ?? 'Libur Kantor',
                    'display' => 'background',
                    'color'   => '#ef4444',
                ];
            }
        }

        return $events;
    }
}