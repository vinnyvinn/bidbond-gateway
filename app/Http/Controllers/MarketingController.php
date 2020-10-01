<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\SendSMS;
use App\Jobs\MassEmails;
use App\Services\CompanyService;
use App\Traits\ApiResponser;

class MarketingController extends Controller
{

    use ApiResponser;

    public $companyService;


    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function createGroup(Request $request)
    {
        return $this->companyService->createGroup($request->all());
    }

    public function attachGroup(Request $request)
    {
        return $this->companyService->attachGroup($request->all());
    }

    public function detachGroup(Request $request)
    {
        return $this->companyService->detachGroup($request->all());
    }

    public function listGroups()
    {
        return $this->companyService->listGroups();
    }

    public function companyByGroupId($id)
    {
        return $this->companyService->listGroups($id);
    }

    public function sendMessage(Request $request) {

        $rules = [
            'group_id' => 'required',
            'message' => 'required',
            'type' => 'required|in:sms,email',
        ];

        $this->validate($request, $rules);

        $members = json_decode($this->companyService->companyByGroupId($request->group_id), true);

        foreach ($members as $key) {

            if ($request->type == 'sms') {
                $this->dispatch(

                    new SendSMS($key['phone_number'], $request->message)
                );
            } else if($request->type == 'email') {

                MassEmails::dispatchNow($key['email'], $request->message);

            } else {

                return $this->errorMessage('message not sent. Try again later', 400);
            }

        }

        return 'success';
    }
}
