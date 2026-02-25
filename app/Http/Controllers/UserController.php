<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('division')->where('role', 'karyawan')->get();
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
            'password' => Hash::make('jonusa123'), // Password default
            'division_id' => $request->division_id,
            'role' => 'karyawan',
            'is_default_password' => true,
        ]);

        return redirect()->back()->with('success', 'Karyawan berhasil didaftarkan dengan password default: jonusa123');
    }
        public function destroy($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $user->delete();
        return redirect()->back()->with('success', 'Karyawan berhasil dihapus!');
    }
}