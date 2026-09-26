<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'department_id',
        'nama_jabatan',
        'standar_gaji_pokok',
    ];

    protected $casts = [
        'standar_gaji_pokok' => 'decimal:2', // Memastikan data berupa desimal untuk perhitungan gaji
    ];

    /**
     * Relasi Belongs-To:
     * Setiap Jabatan (Position) dimiliki oleh satu Departemen.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Relasi One-to-Many:
     * Satu Jabatan bisa diisi oleh banyak Karyawan.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
