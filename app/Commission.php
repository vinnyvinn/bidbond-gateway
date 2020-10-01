<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $guarded = [];

    public function scopeRole($query, $id)
    {
        return $query->where('role_id', $id);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

}
