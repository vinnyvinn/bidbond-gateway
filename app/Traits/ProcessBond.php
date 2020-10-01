<?php

namespace App\Traits;

use App\Jobs\SendBidBond;
use App\Services\BidBondService;
use App\User;
use Illuminate\Support\Facades\Storage;
use PDF;

trait ProcessBond
{
    function processPdf($bidbond, BidBondService $bidBondService): void
    {
        $bid_html = $bidBondService->previewBidBond(['secret' => $bidbond->secret]);
        $pdf = PDF::loadHTML($bid_html)
            ->setOption('margin-top', 32)
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0)
            ->setOption('margin-bottom', 28)
            ->setOption('header-html', view('bidbond.header'))
            ->setOption('footer-html', view('bidbond.footer'))
            ->output();

        Storage::put('public/' . $bidbond->secret . '.pdf', $pdf);

        $user = User::find($bidbond->created_by);

        dispatch(new SendBidBond($user, base64_encode($pdf), $bidbond))->delay(now()->addSeconds(30));
    }
}

