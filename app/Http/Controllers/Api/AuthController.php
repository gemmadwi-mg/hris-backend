<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('hris-token')->plainTextToken;

            // Ambil nama role pertama milik user ini (misal: "HR", "Manager", atau "Karyawan")
            $roleName = $user->roles->first()->name ?? 'Karyawan';
            $user->role = $roleName; // Sisipkan ke object user

            return response()->json([
                'message' => 'Login sukses',
                'user' => $user,
                'token' => $token,
            ], 200);
        }

        return response()->json(['message' => 'Kredensial tidak valid'], 401);
    }

    public function logout(Request $request)
    {
        // Hapus token yang sedang digunakan
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil logout'], 200);
    }

    public function me(Request $request)
    {
        // Mengambil profil user yang sedang login
        return response()->json($request->user());
    }
}
