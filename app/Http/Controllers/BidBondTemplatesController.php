<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\TemplateService;
use App\Traits\ApiResponser;
use Bouncer;

class BidBondTemplatesController extends Controller
{
    public $templateService;

    use ApiResponser;

    public function __construct(TemplateService $templateService)
    {
        $this->templateService = $templateService;
    }

    public function index(Request $request)
    {
        $page = $request->page ?? 1;
        return $this->templateService->obtainTemplates($page);
    }
    public function store(Request $request)
    {
        if (!Bouncer::can('create-bidbond-templates')) {
            abort(403, 'You do not have the rights to create bidbond templates');
        }
        return $this->templateService->createTemplate($request->all());
    }

    public function create()
    {
        return $this->templateService->getParams();
    }

    public function show($secret)
    {
        return $this->templateService->previewTemplate($secret);
    }

    public function edit($secret)
    {
        return $this->templateService->editTemplate($secret);
    }

    public function update(Request $request, $secret)
    {
        if (!Bouncer::can('update-bidbond-templates')) {
            abort(403, 'You do not have the rights to update bidbond templates');
        }
        return $this->templateService->updateTemplate($request->all(), $secret);
    }

    public function destroy($secret)
    {
        if (!Bouncer::can('delete-bidbond-templates')) {
            abort(403, 'You do not have the rights to delete bidbond templates');
        }
        return $this->templateService->deleteTemplate($secret);
    }

}
