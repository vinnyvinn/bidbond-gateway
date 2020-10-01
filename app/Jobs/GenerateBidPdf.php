<?php

namespace App\Jobs;

use App\Services\BidBondService;
use App\Traits\ProcessBond;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateBidPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ProcessBond;

    protected $bidbond;

    public function __construct($bidbond)
    {
        $this->bidbond = $bidbond;
    }

    public function handle(BidBondService $bidBondService)
    {
        $this->processPdf($this->bidbond, $bidBondService);
    }
}
