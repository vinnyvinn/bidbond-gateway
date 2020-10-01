<?php

namespace App\Traits;

trait KycCheck
{
    function kycEnabled(){
        $user = auth()->user();

        $role = $user->roles()->first();

        $kyc_status = isset($role) ? $role->kyc_status()->first() : '';
        return $kyc_status ? $kyc_status->status : 1;
    }
}
