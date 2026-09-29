<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'employee_id', 'bulan', 'tahun', 'gaji_pokok',
        'tunjangan', 'potongan', 'total_gaji_bersih',
        'status', 'tanggal_pembayaran', 'xendit_disbursement_id' // <- Tambahkan di sini
    ];

    protected $casts = [
        'gaji_pokok' => 'decimal:2',
        'tunjangan' => 'decimal:2',
        'potongan' => 'decimal:2',
        'total_gaji_bersih' => 'decimal:2',
        'tanggal_pembayaran' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
