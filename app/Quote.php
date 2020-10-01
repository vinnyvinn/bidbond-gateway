<?php

namespace App;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $appends = ['last_requested'];
    protected $fillable = [
        'email', 'phone', 'tenure', 'amount', 'charge', 'counterparty'
    ];


    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
    public function getLastRequestedAttribute()
    {
        return Carbon::parse($this->created_at)->format('d/m/Y');
    }
}
