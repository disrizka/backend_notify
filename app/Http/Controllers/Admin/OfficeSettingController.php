<?php

namespace App\Http\Controllers\Admin; 

use App\Http\Controllers\Controller;
use App\Models\OfficeSetting; 
use Illuminate\Http\Request;

class OfficeSettingController extends Controller
{
   public function index() {
    $setting = OfficeSetting::first(); 
    return view('admin.attendance.settings', compact('setting')); 
   }

    /**
     * GET /api/attendance/config
     * 
     * Mengembalikan semua konfigurasi kantor yang dibutuhkan Flutter:
     * - Koordinat & radius kantor
     * - Jam masuk + jam pulang + toleransi
     * - Status hari libur
     * - Status radius enforcement (ON/OFF dari toggle admin)
     */
    public function getConfig() {
        $setting = OfficeSetting::first();
        $today = now()->format('Y-m-d');
        
        // Ambil data libur dari database
        $holiday = \DB::table('holidays')->where('holiday_date', $today)->first();
        $isFriday = now()->isFriday(); 

        // Logika penentuan status libur
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
                // Koordinat & radius
                'latitude'         => (double) ($setting->latitude ?? -6.2000),
                'longitude'        => (double) ($setting->longitude ?? 106.8166),
                'radius'           => (double) ($setting->radius ?? 50.0),

                // Jam kerja
                'check_in_time'    => $setting->check_in_time ?? '08:00', 
                'check_out_time'   => $setting->check_out_time ?? '17:00', // ← WAJIB untuk CheckoutScreen
                'late_tolerance'   => (int) ($setting->late_tolerance ?? 15),      

                // Status hari libur (sama seperti CheckIn)
                'is_holiday'       => $isHolidayStatus,
                'holiday_name'     => $name,

                // Radius enforcement toggle (ON = true, OFF = false)
                // Jika true  → karyawan wajib dalam radius untuk auto-approve
                // Jika false → semua bisa absen dari mana saja tapi selalu pending approval
                'radius_enforced'  => (bool) ($setting->radius_enforced ?? true),
            ]
        ]);
    }

   public function update(Request $request)
   {
        $request->validate([
            'latitude'         => 'required',
            'longitude'        => 'required',
            'radius'           => 'required|numeric',
            'check_in_time'    => 'required',
            'check_out_time'   => 'required', 
            'late_tolerance'   => 'required|numeric',
            // radius_enforced boleh tidak ada (checkbox unchecked = tidak terkirim)
        ]);

        OfficeSetting::updateOrCreate(
            ['id' => 1], 
            [
                'latitude'        => $request->latitude,
                'longitude'       => $request->longitude,
                'radius'          => $request->radius,
                'check_in_time'   => $request->check_in_time,
                'check_out_time'  => $request->check_out_time,
                'late_tolerance'  => $request->late_tolerance,
                'radius_enforced' => $request->has('radius_enforced'),
            ]
        );

        return back()->with('success', 'Pengaturan berhasil diperbarui!');
   }
}