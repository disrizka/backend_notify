<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $users = User::with('division')
            ->where('role', 'karyawan')
            ->orderBy('name', 'asc')
            ->get();

        if ($request->expectsJson()) {
            return response()->json($users);
        }

        // Jika akses dari Web Dashboard
        $divisions = Division::all();
        return view('kepala.user.index', compact('users', 'divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'division_id' => 'required|exists:divisions,id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make('jonusa123'), 
            'division_id' => $request->division_id,
            'role' => 'karyawan',
            'is_default_password' => true,
        ]);

        return redirect()->back()->with('success', 'Karyawan berhasil didaftarkan! Password default: jonusa123');
    }

  
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            if ($user->id === auth()->id()) {
                return redirect()->back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
            }

            $user->delete();
            return redirect()->back()->with('success', 'Data karyawan berhasil dihapus dari sistem.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}