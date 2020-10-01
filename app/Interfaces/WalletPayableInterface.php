<?php


namespace App\Interfaces;


interface WalletPayableInterface
{
    public function pay($data);

    public function getBalance();
}
