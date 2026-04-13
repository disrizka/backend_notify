<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use App\Models\Leave;
use App\Models\User;
use App\Models\OfficeSetting;
use App\Notifications\InternalNotification;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Holiday;

class PresenceController extends Controller
{
    /**
     * Hitung jarak antara dua koordinat (meter) menggunakan rumus Haversine
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000; // meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2)
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
           * sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    /**
     * Cek apakah absensi memenuhi syarat auto-approve:
     * 1. Di dalam radius kantor
     * 2. Di dalam jam masuk + toleransi
     * 3. Hari kerja (bukan libur/Jumat)
     * 
     * Return: ['approved' => bool, 'reason' => string]
     */
    private function checkAutoApprove(float $userLat, float $userLng): array
    {
        $setting = OfficeSetting::first();

        if (!$setting) {
            return ['approved' => false, 'reason' => 'Pengaturan kantor belum dikonfigurasi'];
        }

        $today    = now();
        $todayStr = $today->format('Y-m-d');

        // ── 1. Cek hari libur ─────────────────────────────────────────────
        $isFriday  = $today->isFriday();
        $isHoliday = Holiday::where('holiday_date', $todayStr)->exists();

        if ($isFriday || $isHoliday) {
            return ['approved' => false, 'reason' => 'Hari libur — perlu validasi manual'];
        }

        // ── 2. Cek radius lokasi ──────────────────────────────────────────
        $officeLat  = (float) $setting->latitude;
        $officeLng  = (float) $setting->longitude;
        $radius     = (float) ($setting->radius ?? 50);

        $distance = $this->calculateDistance($userLat, $userLng, $officeLat, $officeLng);

        if ($distance > $radius) {
            return [
                'approved' => false,
                'reason'   => sprintf('Di luar radius kantor (%.0f m dari kantor, batas %d m) — perlu validasi manual', $distance, $radius),
                'distance' => round($distance),
            ];
        }

        // ── 3. Cek jam masuk + toleransi ──────────────────────────────────
        $checkInTime   = $setting->check_in_time ?? '08:00';
        $lateTolerance = (int) ($setting->late_tolerance ?? 15);

        // Batas akhir jam masuk = jam masuk + toleransi (dalam menit)
        $deadline = Carbon::createFromFormat('H:i', substr($checkInTime, 0, 5))->addMinutes($lateTolerance);

        // Boleh check-in sejak 2 jam sebelum jam masuk (fleksibel early check-in)
        $earliest = Carbon::createFromFormat('H:i', substr($checkInTime, 0, 5))->subHours(2);

        $nowTime = Carbon::createFromFormat('H:i', now()->format('H:i'));

        if ($nowTime->lt($earliest)) {
            return [
                'approved' => false,
                'reason'   => sprintf('Terlalu awal check-in (sebelum %s) — perlu validasi manual', $earliest->format('H:i')),
            ];
        }

        if ($nowTime->gt($deadline)) {
            return [
                'approved' => false,
                'reason'   => sprintf('Terlambat (batas %s + toleransi %d menit = %s) — perlu validasi manual', substr($checkInTime, 0, 5), $lateTolerance, $deadline->format('H:i')),
            ];
        }

        // ── Semua syarat terpenuhi ─────────────────────────────────────────
        return [
            'approved' => true,
            'reason'   => sprintf('Dalam radius kantor (%.0f m), tepat waktu', $distance),
            'distance' => round($distance),
        ];
    }

    /**
     * 1. Check-In
     */
    public function storeCheckIn(Request $request)
    {
        $user  = $request->user();
        $today = now()->format('Y-m-d');

        // Blokir jika hari libur
        $isHoliday = Holiday::where('holiday_date', $today)->exists();
        if ($isHoliday) {
            return response()->json([
                'success' => false,
                'message' => 'Hari ini kantor libur (Tanggal Merah). Anda tidak perlu absen masuk.',
            ], 422);
        }

        // Cek sudah absen hari ini
        $alreadyCheckedIn = Presence::where('user_id', $user->id)
            ->where('date', $today)
            ->where('category', 'masuk')
            ->exists();

        if ($alreadyCheckedIn) {
            return response()->json(['success' => false, 'message' => 'Anda sudah absen masuk hari ini.'], 422);
        }

        // Simpan foto
        $path = null;
        if ($request->hasFile('photo')) {
            $fileName = time() . '_in_' . $user->id . '.' . $request->file('photo')->getClientOriginalExtension();
            $request->file('photo')->move(public_path('presence_photos'), $fileName);
            $path = 'presence_photos/' . $fileName;
        }

        // ── Auto-approve check ─────────────────────────────────────────────
        $userLat   = (float) $request->latitude;
        $userLng   = (float) $request->longitude;
        $autoCheck = $this->checkAutoApprove($userLat, $userLng);

        $isApproved   = $autoCheck['approved'] ? 'approved' : 'pending';
        $autoApproved = $autoCheck['approved'];

        $presence = Presence::create([
            'user_id'      => $user->id,
            'date'         => $today,
            'category'     => 'masuk',
            'check_in'     => now()->format('H:i:s'),
            'photo_in'     => $path,
            'lat_in'       => $userLat,
            'lng_in'       => $userLng,
            'notes'        => $request->notes ?? 'Absen Masuk Mobile',
            'is_approved'  => $isApproved,
            // Simpan info jarak untuk referensi admin (opsional, skip jika kolom tidak ada)
        ]);

        // Notifikasi ke user
        if ($autoApproved) {
            $user->notify(new InternalNotification([
                'title'   => 'Presensi Masuk Disetujui Otomatis ✓',
                'message' => 'Check-In ' . now()->format('H:i') . ' — dalam radius kantor & tepat waktu.',
                'type'    => 'presence',
            ]));
        } else {
            $user->notify(new InternalNotification([
                'title'   => 'Presensi Masuk Menunggu Persetujuan',
                'message' => 'Check-In ' . now()->format('H:i') . ' — ' . $autoCheck['reason'],
                'type'    => 'presence',
            ]));
        }

        return response()->json([
            'success'       => true,
            'message'       => $autoApproved
                ? 'Check-in berhasil & disetujui otomatis!'
                : 'Check-in berhasil! Menunggu persetujuan admin.',
            'auto_approved' => $autoApproved,
            'status'        => $isApproved,
            'reason'        => $autoCheck['reason'],
            'data'          => $presence,
        ], 201);
    }

    /**
     * 2. Check-Out — juga auto-approve jika dalam radius & jam pulang valid
     */
    public function storeCheckOut(Request $request)
    {
        $user     = $request->user();
        $presence = Presence::where('user_id', $user->id)
            ->where('date', now()->format('Y-m-d'))
            ->where('category', 'masuk')
            ->latest('id')
            ->first();

        if (!$presence) {
            return response()->json(['success' => false, 'message' => 'Data masuk tidak ditemukan hari ini.'], 404);
        }

        if ($request->hasFile('photo')) {
            $fileName = time() . '_out_' . $user->id . '.' . $request->file('photo')->getClientOriginalExtension();
            $request->file('photo')->move(public_path('presence_photos'), $fileName);
            $presence->photo_out = 'presence_photos/' . $fileName;
        }

        $presence->check_out = now()->format('H:i:s');
        $presence->lat_out   = $request->latitude;
        $presence->lng_out   = $request->longitude;
        $presence->notes_out = $request->notes ?? 'Absen Pulang';

        // ── Auto-approve checkout ──────────────────────────────────────────
        $userLat   = (float) $request->latitude;
        $userLng   = (float) $request->longitude;
        $setting   = OfficeSetting::first();

        $autoApprovedOut = false;
        if ($setting) {
            $officeLat = (float) $setting->latitude;
            $officeLng = (float) $setting->longitude;
            $radius    = (float) ($setting->radius ?? 50);
            $distance  = $this->calculateDistance($userLat, $userLng, $officeLat, $officeLng);

            // Check-out: cukup cek dalam radius saja (tidak ada batas jam ketat)
            if ($distance <= $radius) {
                $autoApprovedOut = true;
                $presence->is_approved_out = 'approved';
            } else {
                $presence->is_approved_out = 'pending';
            }
        } else {
            $presence->is_approved_out = 'pending';
        }

        if ($presence->save()) {
            $user->notify(new InternalNotification([
                'title'   => $autoApprovedOut ? 'Presensi Pulang Disetujui Otomatis ✓' : 'Presensi Pulang Menunggu Persetujuan',
                'message' => 'Check-Out jam ' . now()->format('H:i') . ($autoApprovedOut ? ' — dalam radius kantor.' : ' — di luar radius, menunggu admin.'),
                'type'    => 'presence',
            ]));

            return response()->json([
                'success'       => true,
                'message'       => $autoApprovedOut
                    ? 'Check-out berhasil & disetujui otomatis!'
                    : 'Check-out berhasil! Menunggu persetujuan admin.',
                'auto_approved' => $autoApprovedOut,
                'data'          => $presence,
            ], 200);
        }

        return response()->json(['success' => false, 'message' => 'Gagal menyimpan ke database.'], 500);
    }

    /**
     * 3. Izin/Sakit/Cuti
     */
public function storePermission(Request $request)
{
    $user = $request->user();
    
    // 1. Simpan File Dokumen (PDF/Lainnya) jika ada
    $fileDocPath = null;
    if ($request->hasFile('attachment_file')) {
        $fileName = time() . '_doc_' . $user->id . '.' . $request->file('attachment_file')->getClientOriginalExtension();
        $request->file('attachment_file')->move(public_path('leaves/documents'), $fileName);
        $fileDocPath = 'leaves/documents/' . $fileName;
    }

    // 2. Simpan Foto Kamera jika ada
    $filePhotoPath = null;
    if ($request->hasFile('attachment_photo')) {
        $photoName = time() . '_photo_' . $user->id . '.' . $request->file('attachment_photo')->getClientOriginalExtension();
        $request->file('attachment_photo')->move(public_path('leaves/photos'), $photoName);
        $filePhotoPath = 'leaves/photos/' . $photoName;
    }

    // 3. Simpan ke Database
    $leave = Leave::create([
        'user_id'          => $user->id,
        'type'             => strtolower($request->category),
        'start_date'       => $request->start_date,
        'end_date'         => $request->end_date ?? $request->start_date,
        'reason'           => $request->reason,
        'attachment_file'  => $fileDocPath,   // Masuk ke kolom file
        'attachment_photo' => $filePhotoPath, // Masuk ke kolom foto
        'status'           => 'pending',
    ]);

    return response()->json(['success' => true, 'message' => 'Laporan terkirim'], 201);
}

    /**
     * 4. Status hari ini
     */
    public function todayStatus(Request $request)
    {
        try {
            $user  = $request->user();
            $today = now()->format('Y-m-d');

            $isHoliday = \App\Models\Holiday::where('holiday_date', $today)->exists();

            $presenceIn = \App\Models\Presence::where('user_id', $user->id)
                ->whereDate('date', $today)
                ->where('category', 'masuk')
                ->first();

            return response()->json([
                'success'       => true,
                'is_holiday'    => $isHoliday,
                'has_checkin'   => $presenceIn !== null,
                'has_checkout'  => $presenceIn ? ($presenceIn->check_out !== null) : false,
                'check_in'      => $presenceIn ? $presenceIn->check_in : null,
                'check_out'     => $presenceIn ? $presenceIn->check_out : null,
                'is_approved'   => $presenceIn ? $presenceIn->is_approved : null,
                'auto_approved' => $presenceIn ? ($presenceIn->is_approved === 'approved') : false,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 5. Riwayat absensi
     */
    public function history(Request $request)
    {
        $user  = $request->user();
        $month = (int) $request->query('month', now()->month);
        $year  = (int) $request->query('year', now()->year);

        $records = Presence::where('user_id', $user->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($records);
    }
}