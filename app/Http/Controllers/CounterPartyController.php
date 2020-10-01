<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Bouncer;
use App\Services\CounterPartyService;


class CounterPartyController extends Controller
{

    use ApiResponser;

    public $counterPartyService;


    public function __construct(CounterPartyService $counterPartyService)
    {
        $this->counterPartyService = $counterPartyService;
    }

    public function index()
    {
        return $this->counterPartyService->index();
    }

    public function creationDetails()
    {

        return $this->counterPartyService->getCategoriesNPostalCodes();
    }


    public function store(Request $request)
    {
        if (!Bouncer::can('create-counterparties')) {
            abort(403, 'You do not have the rights to create counterparties');
        }

        return $this->counterPartyService->store($request->all());
    }

    public function update(Request $request,$id)
    {
        if (!Bouncer::can('edit-counterparties')) {
            abort(403, 'You do not have the rights to edit counterparties');
        }

        return $this->counterPartyService->update($request->all(),$id);
    }

    public function destroy($id)
    {
        if (!Bouncer::can('create-counterparties')) {
            abort(403, 'You do not have the rights to delete counterparties');
        }
        return $this->counterPartyService->destroy($id);
    }


}
