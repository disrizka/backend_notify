<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class HolidayController extends Controller
{
    public function index(): JsonResponse
    {
        $manualHolidays = Holiday::all()
            ->keyBy('holiday_date')
            ->map(fn($h) => $h->name ?? 'Libur Kantor');

        $result    = [];
        $yearStart = Carbon::now()->startOfYear();
        $yearEnd   = Carbon::now()->endOfYear();

        for ($date = $yearStart->copy(); $date->lte($yearEnd); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');

            // Jumat → otomatis libur
            if ($date->isFriday()) {
                $title = isset($manualHolidays[$dateStr])
                    ? $manualHolidays[$dateStr]
                    : 'Libur Mingguan (Jumat)';

                $result[$dateStr] = ['start' => $dateStr, 'title' => $title];
                unset($manualHolidays[$dateStr]);
                continue;
            }

            // Hari lain yang ada di tabel manual
            if (isset($manualHolidays[$dateStr])) {
                $result[$dateStr] = [
                    'start' => $dateStr,
                    'title' => $manualHolidays[$dateStr],
                ];
                unset($manualHolidays[$dateStr]);
            }
        }

        return response()->json(array_values($result));
    }
}