<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSearch extends Model
{
    protected $database = 'user_searches';
    
    protected $fillable = [
        'id_number','citizenship'
    ];
}
