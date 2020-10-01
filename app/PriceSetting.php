<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PriceSetting extends Model
{
    protected $guarded = [];

    const indemnity_cost = "indemnity_cost";
    const cr12_search_cost = "cr12_search_cost";
    const excise_duty = "excise_duty";

    public static $createRules = [
        'option' => 'bail|required|unique:option',
    ];

    public function scopeOption($builder, $option): void
    {
        $builder->where("option", $option);
    }
}
