<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index()
{
    // 1. Ambil libur manual dari database
    $dbHolidays = \App\Models\Holiday::all();
    
    // 2. Buat koleksi untuk FullCalendar
    $events = [];

    // Tambahkan data dari Database ke Events
    foreach ($dbHolidays as $h) {
        $events[$h->holiday_date] = [
            'start' => $h->holiday_date,
            'display' => 'background',
            'color' => '#ef4444', // Merah
        ];
    }

    $start = now()->startOfYear();
    $end = now()->endOfYear();

    for ($date = $start; $date->lte($end); $date->addDay()) {
        if ($date->isFriday()) {
            $formattedDate = $date->format('Y-m-d');
            
            if (!isset($events[$formattedDate])) {
                $events[$formattedDate] = [
                    'start' => $formattedDate,
                    'display' => 'background',
                    'color' => '#ef4444',
                ];
            }
        }
    }

    $holidays = array_values($events);
    return view('admin.presence.schedule', compact('holidays'));
}

public function toggle(Request $request)
{
    $date = $request->date;
    $dayName = \Carbon\Carbon::parse($date)->format('l'); 

    $holiday = \App\Models\Holiday::where('holiday_date', $date)->first();

    if ($holiday) {
        $holiday->delete();
        return response()->json(['status' => 'success', 'message' => 'Sekarang menjadi hari masuk']);
    } else {
        \App\Models\Holiday::create([
            'holiday_date' => $date,
            'name' => 'Libur Kantor'
        ]);
        return response()->json(['status' => 'success', 'message' => 'Sekarang menjadi hari libur']);
    }
}
}