<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Position;

class PositionController extends Controller
{
    public function index()
    {
        // Mengambil semua jabatan dan memuat relasi departemennya
        $positions = Position::with('department')->orderBy('nama_jabatan')->get();
        return response()->json($positions);
    }
}
