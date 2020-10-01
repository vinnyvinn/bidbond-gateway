<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendQuote extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $bond_amount;
    public $charge;
    public $tenure;
    public $bidcost;
    public $dutyamount;
    public $counterparty;
    public $idemnity_cost;

    public function __construct($user, $bond_amount, $bidcost, $tenure, $dutyamount, $charge, $counterparty,$idemnity_cost)
    {


        $this->user = $user;
        $this->bond_amount = $bond_amount;
        $this->bidcost = $bidcost;
        $this->tenure = $tenure;
        $this->dutyamount = $dutyamount;
        $this->charge = $charge;
        $this->counterparty = $counterparty;
        $this->idemnity_cost = $idemnity_cost;
    }

    public function build()
    {

        return $this->markdown('emails.quote')
            ->subject(config('app.name') . " quote");
    }
}
