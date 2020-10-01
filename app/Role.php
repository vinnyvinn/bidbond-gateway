<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Role extends Model
{
    protected $fillable = ['name', 'kyc_status'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'assigned_roles', 'role_id', 'entity_id');
    }

    public function kyc_status()
    {
        return $this->hasOne(KycStatus::class);
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = empty($value) ? $value : Str::slug($value, "_");
    }
}
