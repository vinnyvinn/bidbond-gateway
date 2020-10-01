<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KycStatus extends Model
{
    protected $fillable = ['role_id','status'];

    public function role(){
    	return $this->belongsTo(Role::class);
    }
}
