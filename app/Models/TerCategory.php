<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerCategory extends Model
{
    use HasFactory;

    protected $table = 'ter_categories';

    protected $primaryKey = 'ter_category_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'ter_category_id',
        'category_name',
        'description',
    ];
}