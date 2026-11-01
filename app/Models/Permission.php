<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    // WAJIB: Deklarasi Custom Primary Key String
    protected $primaryKey = 'permission_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['permission_id', 'name', 'guard_name'];

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'role_permissions',
            'permission_id',
            'role_id',
            'permission_id',
            'role_id'
        );
    }
}