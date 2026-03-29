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

   public function getConfig() {
    $setting = OfficeSetting::first(); 
    return response()->json([
        'data' => [
            'latitude' => $setting->latitude,
            'longitude' => $setting->longitude,
            'radius' => $setting->radius,
            'check_in_time' => $setting->check_in_time, 
            'check_out_time' => $setting->check_out_time, 
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
        'check_out_time' => 'required', 
        'late_tolerance' => 'required|numeric',
    ]);

    OfficeSetting::updateOrCreate(
        ['id' => 1], 
        [
            'latitude'       => $request->latitude,
            'longitude'      => $request->longitude,
            'radius'         => $request->radius,
            'check_in_time'  => $request->check_in_time,
            'check_out_time' => $request->check_out_time,
            'late_tolerance' => $request->late_tolerance,
        ]
    );

    return back()->with('success', 'Pengaturan berhasil diperbarui!');
   }
}