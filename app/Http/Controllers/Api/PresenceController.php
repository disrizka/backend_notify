<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use App\Models\Leave;
use App\Models\User;
use App\Notifications\InternalNotification;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PresenceController extends Controller
{
    /**
     * 1. Check-In (Masuk ke Tabel Presences + Notifikasi)
     */
    public function storeCheckIn(Request $request)
    {
        $user = $request->user();
        $today = now()->format('Y-m-d');

        // Cek apakah hari ini sudah absen masuk
        $alreadyCheckedIn = Presence::where('user_id', $user->id)
            ->where('date', $today)
            ->where('category', 'masuk')
            ->exists();

        if ($alreadyCheckedIn) {
            return response()->json(['success' => false, 'message' => 'Anda sudah absen masuk hari ini.'], 422);
        }

        $path = null;
        if ($request->hasFile('photo')) {
            // Gunakan move ke public agar mudah diakses di Windows/Flutter
            $fileName = time() . '_in_' . $user->id . '.' . $request->file('photo')->getClientOriginalExtension();
            $request->file('photo')->move(public_path('presence_photos'), $fileName);
            $path = 'presence_photos/' . $fileName;
        }

        $presence = Presence::create([
            'user_id'     => $user->id,
            'date'        => $today,
            'category'    => 'masuk',
            'check_in'    => now()->format('H:i:s'),
            'photo_in'    => $path,
            'lat_in'      => $request->latitude,
            'lng_in'      => $request->longitude,
            'notes'       => $request->notes ?? 'Absen Masuk Mobile',
            'is_approved' => 'pending'
        ]);

        // KIRIM NOTIFIKASI KE USER SENDIRI (Konfirmasi)
        $user->notify(new InternalNotification([
            'title'   => 'Presensi Masuk Berhasil',
            'message' => 'Anda berhasil Check-In pada jam ' . now()->format('H:i'),
            'type'    => 'presence'
        ]));

        return response()->json(['success' => true, 'message' => 'Check-in berhasil!', 'data' => $presence], 201);
    }

    /**
     * 2. Check-Out (Update Tabel Presences + Notifikasi)
     */
    public function storeCheckOut(Request $request)
    {
        $user = $request->user();
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

        if ($presence->save()) {
            // KIRIM NOTIFIKASI
            $user->notify(new InternalNotification([
                'title'   => 'Presensi Pulang Berhasil',
                'message' => 'Terima kasih, Anda telah Check-Out jam ' . now()->format('H:i'),
                'type'    => 'presence'
            ]));

            return response()->json(['success' => true, 'message' => 'Check-out berhasil!', 'data' => $presence], 200);
        }

        return response()->json(['success' => false, 'message' => 'Gagal menyimpan ke database.'], 500);
    }

    /**
     * 3. Izin/Sakit/Cuti (Masuk ke Tabel Leaves + Notifikasi)
     */
    public function storePermission(Request $request)
    {
        $user = $request->user();
        $startDate = $request->start_date;

        $alreadyPresent = Presence::where('user_id', $user->id)
            ->whereDate('date', $startDate)
            ->where('category', 'masuk')
            ->exists();

        if ($alreadyPresent) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal! Anda sudah memiliki catatan absen masuk pada tanggal ' . $startDate
            ], 422);
        }

        $attachment = null;
        if ($request->hasFile('attachment')) {
            $fileName = time() . '_leave_' . $user->id . '.' . $request->file('attachment')->getClientOriginalExtension();
            $request->file('attachment')->move(public_path('leaves'), $fileName);
            $attachment = 'leaves/' . $fileName;
        }

        $leave = Leave::create([
            'user_id'         => $user->id,
            'type'            => strtolower($request->category), 
            'start_date'      => $request->start_date,
            'end_date'        => $request->end_date ?? $request->start_date,
            'reason'          => $request->notes, 
            'attachment_file' => $attachment, 
            'status'          => 'pending',
        ]);

        // KIRIM NOTIFIKASI
        $user->notify(new InternalNotification([
            'title'   => 'Pengajuan ' . ucfirst($request->category) . ' Terkirim',
            'message' => 'Laporan Anda sedang menunggu persetujuan admin.',
            'type'    => 'presence'
        ]));

        return response()->json(['success' => true, 'message' => 'Laporan berhasil terkirim'], 201);
    }

    // --- Fungsi Status & History Tetap Sama ---
    public function todayStatus(Request $request)
    {
        $user  = $request->user();
        $today = now()->format('Y-m-d');

        $presenceIn = Presence::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->where('category', 'masuk')
            ->first();

        return response()->json([
            'has_checkin'  => $presenceIn !== null,
            'has_checkout' => $presenceIn?->check_out !== null,
            'check_in'     => $presenceIn?->check_in,
            'check_out'    => $presenceIn?->check_out,
        ]);
    }

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