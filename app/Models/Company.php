<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $table = 'companies'; // Sekarang menggunakan tabel 'companies'

    protected $primaryKey = 'company_id'; // Sesuai kolom di migrasi
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'company_name',
        'email',
        'phone_number',
        'clock_in_time',
        'clock_out_time',
        'late_penalty',
        'bpjs_health_percent',
        'bpjs_jht_percent',
        'bpjs_jp_percent',
        'bpjs_health_salary_limit',
        'bpjs_jp_salary_limit',
    ];

}