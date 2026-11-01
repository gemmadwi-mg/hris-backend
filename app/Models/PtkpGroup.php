<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PtkpGroup extends Model
{
    use HasFactory;

    protected $table = 'ptkp_groups';

    protected $primaryKey = 'ptkp_group_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'ptkp_group_id',
        'group_name',
        'description',
        'annual_amount',
        'ter_category_id',
    ];

    // Relasi: Setiap Golongan PTKP masuk ke 1 Kategori TER
    public function terCategory()
    {
        return $this->belongsTo(TerCategory::class, 'ter_category_id', 'ter_category_id');
    }
}