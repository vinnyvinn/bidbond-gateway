<?php

namespace App;


use Illuminate\Database\Eloquent\Model;
use App\Traits\AddsExpiry;

class Code extends Model
{
    use AddsExpiry;

    protected $fillable = [
        'code_email', 'code_phone', 'count', 'phone_number', 'email'
    ];

    protected $dates = ['email_code_expiry'];

    public function expired(): bool
    {
        return $this->email_code_expiry->isPast();
    }

    public function limitExceeded(): bool
    {
        return $this->count > 3;
    }
}
