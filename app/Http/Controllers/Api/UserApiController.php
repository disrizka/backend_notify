<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserApiController extends Controller
{
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password'      => 'required',
            'new_password'          => 'required|min:6|confirmed',
            'new_password_confirmation' => 'required',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Password lama tidak sesuai.'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'message' => 'Password berhasil diubah.'
        ], 200);
    }
}