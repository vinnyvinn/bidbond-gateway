<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RowCommision extends Model
{
    protected $fillable = [
        'user_id', 'commission_amount', 'commission_type', 'bidbond_id'
    ];
    
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function scopeBidbond($query, $id)
    {
        return $query->where('bidbond_id', $id);
    }

    public function scopeOfUser($query, $id)
    {
        return $query->where('user_id', $id);
    }
}
