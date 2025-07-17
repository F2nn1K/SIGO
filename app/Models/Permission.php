<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';
    
    protected $fillable = [
        'name',
        'code',
        'description'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function profiles()
    {
        return $this->belongsToMany(Profile::class, 'profile_permissions', 'permission_id', 'profile_id');
    }

    public function group()
    {
        return $this->belongsTo(PermissionGroup::class, 'group_id');
    }
}