<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // 1. Ambil data karyawan yang sedang login
        $user = $request->user();

        // 2. Jika belum login atau model tidak memiliki fungsi hasPermission, tolak!
        if (!$user || !method_exists($user, 'hasPermission')) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Silakan login terlebih dahulu.'
            ], 401);
        }

        // 3. Gunakan fungsi hasPermission dari Model Employee untuk mengecek akses
        if (!$user->hasPermission($permission)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses ('.$permission.') untuk melakukan tindakan ini.'
            ], 403);
        }

        // 4. Lolos! Lanjutkan request ke Controller
        return $next($request);
    }
}