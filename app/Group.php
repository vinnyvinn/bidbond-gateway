<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Group extends Model
{
    protected $guarded = [];
    protected $table = 'groups';

    public static $createRules = [
        'name' => 'bail|required|unique:groups,name',
    ];

    public function agents()
    {
        return $this->hasMany(Agent::class);
    }

    public function bidbondprices()
    {
        return $this->hasMany(BidbondPrice::class);
    }

    public function scopeOfName($builder, $group): void
    {
        $builder->where("name", $group);
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = empty($value) ? $value : Str::slug($value, "_");
    }
}
