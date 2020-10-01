<?php

namespace App\Interfaces;


interface BankingInterface
{
    public function getConversionRate($data);

    public function applyBidBond();
}
