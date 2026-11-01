<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // WAJIB: Deklarasi Custom Primary Key String
    protected $primaryKey = 'role_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['role_id', 'role_name'];

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permissions',
            'role_id',          // Foreign key di role_permissions
            'permission_id',    // Related key di role_permissions
            'role_id',          // Local key di roles
            'permission_id'     // Local key di permissions
        );
    }
}