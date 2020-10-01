<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserCache extends Model
{
    protected $fillable = [
        'last_name', 'phone_numbers', 'middle_name', 'id_number', 'gender', 'first_name', 'dob','kra_pin',
        'citizenship'
    ];

    protected $casts = ['phone_numbers' => 'array'];
}
