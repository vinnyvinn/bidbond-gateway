<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    const bidbond_total = "bidbond_total";
    const bank_limit = "bank_limit";
    const company_limit = "company_limit";
    const mpf = "mpf";
    const other = "other";

    protected $fillable = [
        'option', 'value'
    ];

    public function scopeOption($builder, $option): void
    {
        $builder->where("option", $option);
    }
}
