<?php

namespace App\Http\Controllers\Admin; 

use App\Http\Controllers\Controller;
use App\Models\OfficeSetting; 
use Illuminate\Http\Request;

class OfficeSettingController extends Controller
{
   // 1. UNTUK TAMPILAN WEB ADMIN (Browser)
   public function index() {
    $setting = OfficeSetting::first(); 
    return view('admin.attendance.settings', compact('setting')); 
   }

   // 2. KHUSUS UNTUK API FLUTTER (Sudah tambah check_out_time)
   public function getConfig() {
    $setting = OfficeSetting::first(); 
    return response()->json([
        'data' => [
            'latitude' => $setting->latitude,
            'longitude' => $setting->longitude,
            'radius' => $setting->radius,
            'check_in_time' => $setting->check_in_time, 
            'check_out_time' => $setting->check_out_time, // TAMBAHKAN INI
            'late_tolerance' => $setting->late_tolerance,
        ]
    ]);
   }

   public function update(Request $request)
   {
    $request->validate([
        'latitude' => 'required',
        'longitude' => 'required',
        'radius' => 'required|numeric',
        'check_in_time' => 'required',
        'check_out_time' => 'required', // TAMBAHKAN VALIDASI
        'late_tolerance' => 'required|numeric',
    ]);

    OfficeSetting::updateOrCreate(
        ['id' => 1], 
        [
            'latitude'       => $request->latitude,
            'longitude'      => $request->longitude,
            'radius'         => $request->radius,
            'check_in_time'  => $request->check_in_time,
            'check_out_time' => $request->check_out_time, // SIMPAN KE DATABASE
            'late_tolerance' => $request->late_tolerance,
        ]
    );

    return back()->with('success', 'Pengaturan berhasil diperbarui!');
   }
}