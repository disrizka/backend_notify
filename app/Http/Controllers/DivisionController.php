<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    // Menampilkan daftar divisi & setting step
    public function index()
    {
        $divisions = Division::all();
        return view('kepala.division.index', compact('divisions'));
    }

    // Simpan divisi baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Division::create([
            'name' => $request->name,
            // Step diisi default dulu, nanti bisa di-update
        ]);

        return redirect()->back()->with('success', 'Divisi berhasil ditambahkan!');
    }

      public function update(Request $request, Division $division)
{
    $data = $request->all();

    for ($i = 1; $i <= 4; $i++) {
        $data["req_photo_$i"] = $request->has("req_photo_$i");
        $data["req_video_$i"] = $request->has("req_video_$i");
        $data["req_desc_$i"] = $request->has("req_desc_$i"); 
    }

    $division->update($data);
    return redirect()->back()->with('success', 'Detail alur kerja divisi diperbarui!');
}
}