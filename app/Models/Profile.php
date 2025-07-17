<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $table = 'profiles';
    
    protected $fillable = ['name', 'description'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'profile_permissions', 'profile_id', 'permission_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'profile_id');
    }
}