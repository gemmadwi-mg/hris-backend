<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * PROSES LOGIN
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        $employee = Employee::with('role')->where('email', $request->email)->first();

        if (!$employee || !Hash::check($request->password, $employee->password)) {
            return response()->json(['success' => false, 'message' => 'Email atau Password salah'], 401);
        }

        $token = $employee->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'employee' => $employee,
                'token' => $token,
            ]
        ], 200);
    }

    /**
     * PROSES LUPA PASSWORD (Kirim Email)
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:employees,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Email tidak terdaftar'], 404);
        }

        // Menggunakan broker 'employees' yang sudah kita set di config/auth.php
        $status = Password::broker('employees')->sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['success' => true, 'message' => 'Link reset password telah dikirim ke email Anda'], 200);
        }

        return response()->json(['success' => false, 'message' => 'Gagal mengirim link reset password'], 500);
    }

    /**
     * PROSES RESET PASSWORD (Submit Password Baru)
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed', // Pastikan Vue mengirim 'password' dan 'password_confirmation'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        $status = Password::broker('employees')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['success' => true, 'message' => 'Password berhasil direset. Silakan login kembali.'], 200);
        }

        return response()->json(['success' => false, 'message' => 'Token tidak valid atau kadaluarsa'], 400);
    }

    /**
     * PROSES LOGOUT
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true, 'message' => 'Logout berhasil'], 200);
    }
}