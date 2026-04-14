<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use App\Models\Leave;
use App\Models\OfficeSetting;
use App\Notifications\InternalNotification;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Holiday;

class PresenceController extends Controller
{
    /**
     * Hitung jarak dua koordinat (meter) — Haversine
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $R    = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Cek syarat auto-approve untuk CHECK-IN
     *
     * Logika radius_enforced:
     *   ON  (true)  → di luar radius = TOLAK (return blocked=true), tidak bisa absen
     *   OFF (false) → semua bisa absen, tapi approved=false (pending)
     *
     * Return array:
     *   'blocked'  => bool  — apakah request harus ditolak sama sekali (radius ON, luar radius)
     *   'approved' => bool  — apakah bisa auto-approve
     *   'reason'   => string
     */
    private function checkAutoApproveCheckIn(float $userLat, float $userLng): array
    {
        $setting = OfficeSetting::first();

        if (!$setting) {
            return ['blocked' => false, 'approved' => false, 'reason' => 'Konfigurasi kantor belum diatur'];
        }

        $today    = now();
        $todayStr = $today->format('Y-m-d');

        // ── 1. Hari libur ──────────────────────────────────────────────────
        if ($today->isFriday() || Holiday::where('holiday_date', $todayStr)->exists()) {
            return ['blocked' => false, 'approved' => false, 'reason' => 'Hari libur — perlu validasi manual'];
        }

        $officeLat       = (float) $setting->latitude;
        $officeLng       = (float) $setting->longitude;
        $radius          = (float) ($setting->radius ?? 50);
        $radiusEnforced  = (bool)  ($setting->radius_enforced ?? true);

        $distance = $this->calculateDistance($userLat, $userLng, $officeLat, $officeLng);
        $inRadius = $distance <= $radius;

        // ── 2. Cek radius ──────────────────────────────────────────────────
        if (!$inRadius) {
            if ($radiusEnforced) {
                // Radius ON + di luar → TOLAK TOTAL (blocked)
                return [
                    'blocked' => true,
                    'approved' => false,
                    'reason'   => sprintf(
                        'Absen ditolak: Anda berada %.0f m dari kantor (batas radius %d m). Harap absen dari dalam area kantor.',
                        $distance, $radius
                    ),
                    'distance' => round($distance),
                ];
            } else {
                // Radius OFF + di luar → boleh, tapi pending
                return [
                    'blocked' => false,
                    'approved' => false,
                    'reason'   => sprintf(
                        'Di luar radius kantor (%.0f m) — mode bebas radius aktif, menunggu persetujuan admin.',
                        $distance
                    ),
                    'distance' => round($distance),
                ];
            }
        }

        // ── 3. Radius ON + dalam radius → cek jam masuk ───────────────────
        if ($radiusEnforced) {
            $checkInTime   = $setting->check_in_time ?? '08:00';
            $lateTolerance = (int) ($setting->late_tolerance ?? 15);
            $deadline      = Carbon::createFromFormat('H:i', substr($checkInTime, 0, 5))->addMinutes($lateTolerance);
            $earliest      = Carbon::createFromFormat('H:i', substr($checkInTime, 0, 5))->subHours(2);
            $nowTime       = Carbon::createFromFormat('H:i', now()->format('H:i'));

            if ($nowTime->lt($earliest)) {
                return [
                    'blocked' => false,
                    'approved' => false,
                    'reason'   => sprintf('Terlalu awal check-in (sebelum %s)', $earliest->format('H:i')),
                ];
            }

            if ($nowTime->gt($deadline)) {
                return [
                    'blocked' => false,
                    'approved' => false,
                    'reason'   => sprintf(
                        'Terlambat — batas check-in %s + toleransi %d menit = %s',
                        substr($checkInTime, 0, 5), $lateTolerance, $deadline->format('H:i')
                    ),
                ];
            }

            return [
                'blocked' => false,
                'approved' => true,
                'reason'   => sprintf('Dalam radius (%.0f m) & tepat waktu ✓', $distance),
                'distance' => round($distance),
            ];
        }

        // Radius OFF + dalam radius → tetap pending (enforcement nonaktif)
        return [
            'blocked' => false,
            'approved' => false,
            'reason'   => 'Mode bebas radius — menunggu persetujuan admin.',
            'distance' => round($distance),
        ];
    }

    /**
     * Cek syarat auto-approve untuk CHECK-OUT
     * (Tidak ada cek jam ketat untuk checkout, hanya radius)
     */
    private function checkAutoApproveCheckOut(float $userLat, float $userLng): array
    {
        $setting = OfficeSetting::first();
        if (!$setting) {
            return ['blocked' => false, 'approved' => false, 'reason' => 'Konfigurasi kantor belum diatur'];
        }

        $officeLat      = (float) $setting->latitude;
        $officeLng      = (float) $setting->longitude;
        $radius         = (float) ($setting->radius ?? 50);
        $radiusEnforced = (bool)  ($setting->radius_enforced ?? true);

        $distance = $this->calculateDistance($userLat, $userLng, $officeLat, $officeLng);
        $inRadius = $distance <= $radius;

        if (!$inRadius) {
            if ($radiusEnforced) {
                // Radius ON + di luar → TOLAK TOTAL
                return [
                    'blocked' => true,
                    'approved' => false,
                    'reason'   => sprintf(
                        'Absen pulang ditolak: Anda berada %.0f m dari kantor (batas radius %d m).',
                        $distance, $radius
                    ),
                    'distance' => round($distance),
                ];
            } else {
                // Radius OFF + di luar → pending
                return [
                    'blocked' => false,
                    'approved' => false,
                    'reason'   => sprintf(
                        'Di luar radius (%.0f m) — mode bebas radius, menunggu persetujuan admin.',
                        $distance
                    ),
                    'distance' => round($distance),
                ];
            }
        }

        if ($radiusEnforced) {
            // Radius ON + dalam radius → auto-approve checkout
            return [
                'blocked' => false,
                'approved' => true,
                'reason'   => sprintf('Dalam radius kantor (%.0f m) ✓', $distance),
                'distance' => round($distance),
            ];
        }

        // Radius OFF + dalam radius → tetap pending
        return [
            'blocked' => false,
            'approved' => false,
            'reason'   => 'Mode bebas radius — menunggu persetujuan admin.',
            'distance' => round($distance),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. Check-In
    // ─────────────────────────────────────────────────────────────────────────
    public function storeCheckIn(Request $request)
    {
        $user  = $request->user();
        $today = now()->format('Y-m-d');

        // Blokir hari libur
        if (Holiday::where('holiday_date', $today)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Hari ini kantor libur. Tidak perlu absen.',
            ], 422);
        }

        // Cek sudah absen hari ini
        if (Presence::where('user_id', $user->id)->where('date', $today)->where('category', 'masuk')->exists()) {
            return response()->json(['success' => false, 'message' => 'Anda sudah absen masuk hari ini.'], 422);
        }

        $userLat   = (float) $request->latitude;
        $userLng   = (float) $request->longitude;
        $autoCheck = $this->checkAutoApproveCheckIn($userLat, $userLng);

        // Jika radius ON dan di luar → TOLAK dengan 422
        if ($autoCheck['blocked']) {
            return response()->json([
                'success' => false,
                'message' => $autoCheck['reason'],
                'blocked' => true,
            ], 422);
        }

        // Simpan foto
        $path = null;
        if ($request->hasFile('photo')) {
            $fileName = time() . '_in_' . $user->id . '.' . $request->file('photo')->getClientOriginalExtension();
            $request->file('photo')->move(public_path('presence_photos'), $fileName);
            $path = 'presence_photos/' . $fileName;
        }

        $isApproved   = $autoCheck['approved'] ? 'approved' : 'pending';
        $autoApproved = $autoCheck['approved'];

        $presence = Presence::create([
            'user_id'     => $user->id,
            'date'        => $today,
            'category'    => 'masuk',
            'check_in'    => now()->format('H:i:s'),
            'photo_in'    => $path,
            'lat_in'      => $userLat,
            'lng_in'      => $userLng,
            'notes'       => $request->notes ?? 'Absen Masuk Mobile',
            'is_approved' => $isApproved,
        ]);

        $user->notify(new InternalNotification([
            'title'   => $autoApproved
                ? 'Presensi Masuk Disetujui Otomatis ✓'
                : 'Presensi Masuk Menunggu Persetujuan',
            'message' => 'Check-In ' . now()->format('H:i') . ' — ' . $autoCheck['reason'],
            'type'    => 'presence',
        ]));

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

    // ─────────────────────────────────────────────────────────────────────────
    // 2. Check-Out
    // ─────────────────────────────────────────────────────────────────────────
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

        $userLat   = (float) $request->latitude;
        $userLng   = (float) $request->longitude;
        $autoCheck = $this->checkAutoApproveCheckOut($userLat, $userLng);

        // Jika radius ON dan di luar → TOLAK dengan 422
        if ($autoCheck['blocked']) {
            return response()->json([
                'success' => false,
                'message' => $autoCheck['reason'],
                'blocked' => true,
            ], 422);
        }

        // Simpan foto
        if ($request->hasFile('photo')) {
            $fileName = time() . '_out_' . $user->id . '.' . $request->file('photo')->getClientOriginalExtension();
            $request->file('photo')->move(public_path('presence_photos'), $fileName);
            $presence->photo_out = 'presence_photos/' . $fileName;
        }

        $presence->check_out       = now()->format('H:i:s');
        $presence->lat_out         = $userLat;
        $presence->lng_out         = $userLng;
        $presence->notes_out       = $request->notes ?? 'Absen Pulang';
        $presence->is_approved_out = $autoCheck['approved'] ? 'approved' : 'pending';

        if ($presence->save()) {
            $user->notify(new InternalNotification([
                'title'   => $autoCheck['approved']
                    ? 'Presensi Pulang Disetujui Otomatis ✓'
                    : 'Presensi Pulang Menunggu Persetujuan',
                'message' => 'Check-Out ' . now()->format('H:i') . ' — ' . $autoCheck['reason'],
                'type'    => 'presence',
            ]));

            return response()->json([
                'success'       => true,
                'message'       => $autoCheck['approved']
                    ? 'Check-out berhasil & disetujui otomatis!'
                    : 'Check-out berhasil! Menunggu persetujuan admin.',
                'auto_approved' => $autoCheck['approved'],
                'reason'        => $autoCheck['reason'],
                'data'          => $presence,
            ], 200);
        }

        return response()->json(['success' => false, 'message' => 'Gagal menyimpan ke database.'], 500);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. Izin / Sakit / Cuti
    // ─────────────────────────────────────────────────────────────────────────
    public function storePermission(Request $request)
    {
        $user = $request->user();

        $fileDocPath   = null;
        $filePhotoPath = null;

        if ($request->hasFile('attachment_file')) {
            $n = time() . '_doc_' . $user->id . '.' . $request->file('attachment_file')->getClientOriginalExtension();
            $request->file('attachment_file')->move(public_path('leaves/documents'), $n);
            $fileDocPath = 'leaves/documents/' . $n;
        }

        if ($request->hasFile('attachment_photo')) {
            $n = time() . '_photo_' . $user->id . '.' . $request->file('attachment_photo')->getClientOriginalExtension();
            $request->file('attachment_photo')->move(public_path('leaves/photos'), $n);
            $filePhotoPath = 'leaves/photos/' . $n;
        }

        Leave::create([
            'user_id'          => $user->id,
            'type'             => strtolower($request->category),
            'start_date'       => $request->start_date,
            'end_date'         => $request->end_date ?? $request->start_date,
            'reason'           => $request->reason,
            'attachment_file'  => $fileDocPath,
            'attachment_photo' => $filePhotoPath,
            'status'           => 'pending',
        ]);

        return response()->json(['success' => true, 'message' => 'Laporan terkirim'], 201);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. Status hari ini
    // ─────────────────────────────────────────────────────────────────────────
    public function todayStatus(Request $request)
    {
        try {
            $user  = $request->user();
            $today = now()->format('Y-m-d');

            $isHoliday  = Holiday::where('holiday_date', $today)->exists();
            $presenceIn = Presence::where('user_id', $user->id)
                ->whereDate('date', $today)
                ->where('category', 'masuk')
                ->first();

            return response()->json([
                'success'       => true,
                'is_holiday'    => $isHoliday,
                'has_checkin'   => $presenceIn !== null,
                'has_checkout'  => $presenceIn ? ($presenceIn->check_out !== null) : false,
                'check_in'      => $presenceIn ? $presenceIn->check_in  : null,
                'check_out'     => $presenceIn ? $presenceIn->check_out : null,
                'is_approved'   => $presenceIn ? $presenceIn->is_approved : null,
                'auto_approved' => $presenceIn ? ($presenceIn->is_approved === 'approved') : false,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. Riwayat absensi
    // ─────────────────────────────────────────────────────────────────────────
    public function history(Request $request)
    {
        $user  = $request->user();
        $month = (int) $request->query('month', now()->month);
        $year  = (int) $request->query('year', now()->year);

        return response()->json(
            Presence::where('user_id', $user->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->orderBy('date', 'desc')
                ->get()
        );
    }
}