<?php

namespace App\Http\Controllers;


use App\Services\BidBondService;
use Illuminate\Http\Request;
use PDF;

class TestController extends Controller
{
    public function viewBidbondPdfResponse(Request $request)
    {
        $bidBondService = new BidBondService();
        $bid_html = $bidBondService->previewBidBond(['secret' => $request->secret]);

        return PDF::loadHTML($bid_html)
            ->setOption('margin-top', 32)
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0)
            ->setOption('margin-bottom', 28)
            ->setOption('header-html', view('bidbond.header'))
            ->setOption('footer-html', view('bidbond.footer'))
            ->inline();
    }
}
