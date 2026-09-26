<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'nama_departemen',
        'kode_departemen',
    ];

    /**
     * Relasi One-to-Many:
     * Satu Departemen bisa memiliki banyak Jabatan (Position).
     */
    public function positions()
    {
        return $this->hasMany(Position::class);
    }
}
