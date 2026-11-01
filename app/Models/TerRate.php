<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerRate extends Model
{
    use HasFactory;

    protected $table = 'ter_rates';

    protected $primaryKey = 'ter_rate_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'ter_rate_id',
        'ter_category_id',
        'lower_bound',
        'upper_bound',
        'rate',
    ];

    // Relasi: Tarif ini milik kategori TER mana? (A, B, atau C)
    public function terCategory()
    {
        return $this->belongsTo(TerCategory::class, 'ter_category_id', 'ter_category_id');
    }
}