<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $table = 'branches';

    protected $primaryKey = 'branch_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'branch_id',
        'company_id',
        'branch_name',
        'address',
        'latitude',
        'longitude',
        'radius_meters',
    ];

    // Relasi: Cabang ini milik Perusahaan apa?
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }
}