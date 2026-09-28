<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function employee()
    {
        // Hubungkan kembali ke tabel relasi User jika diperlukan
        return $this->belongsTo(Employee::class)->with('user');
    }
}
