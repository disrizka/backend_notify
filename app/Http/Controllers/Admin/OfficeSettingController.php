<?php

namespace App\Http\Controllers\Admin; 

use App\Http\Controllers\Controller;
use App\Models\OfficeSetting; 
use Illuminate\Http\Request;

class OfficeSettingController extends Controller
{
   public function index() {
    $setting = OfficeSetting::first(); 

      \Log::info('OFFICE SETTING:', $setting ? $setting->toArray() : ['null' => true]);
    return view('admin.attendance.settings', compact('setting')); 
   }

    public function getConfig() {
        $setting = OfficeSetting::first();
        $today = now()->format('Y-m-d');
        
        $holiday = \DB::table('holidays')->where('holiday_date', $today)->first();
        $isFriday = now()->isFriday(); 

        $isHolidayStatus = ($holiday || $isFriday) ? true : false;
        $name = '';
        if ($isFriday) {
            $name = 'Libur Mingguan (Jumat)';
        } elseif ($holiday) {
            $name = $holiday->name ?? 'Libur Kantor';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'latitude'         => (double) ($setting->latitude ?? -6.2000),
                'longitude'        => (double) ($setting->longitude ?? 106.8166),
                'radius'           => (double) ($setting->radius ?? 50.0),

                'check_in_time'    => $setting->check_in_time ?? '08:00', 
                'check_out_time'   => $setting->check_out_time ?? '17:00',
                'late_tolerance'   => (int) ($setting->late_tolerance ?? 15),      

                'is_holiday'       => $isHolidayStatus,
                'holiday_name'     => $name,
                'radius_enforced'  => (bool) ($setting->radius_enforced ?? true),
            ]
        ]);
    }

 public function update(Request $request)
    {
        $setting = \App\Models\OfficeSetting::find(1);

        $setting->fill([
            'latitude'       => $_POST['latitude'], 
            'longitude'      => $request->longitude,
            'radius'         => $request->radius,
            'check_in_time'  => $request->check_in_time, 
            'check_out_time' => $request->check_out_time,
            'late_tolerance' => $request->late_tolerance,
        ]);
        if ($request->radius_enforced == 'on') {
            $setting->radius_enforced = true;
        } else {
            $setting->radius_enforced = false;
        }

        return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui!');
    }
}