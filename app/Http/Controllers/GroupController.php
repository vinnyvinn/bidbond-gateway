<?php

namespace App\Http\Controllers;

use App\Agent;
use App\Group;
use App\Services\CompanyService;
use Illuminate\Http\Request;
use Bouncer;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{

    public $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function index()
    {
        return response()->json(Group::all());
    }

    public function store(Request $request)
    {
        if (!Bouncer::can('create-bidbond-pricing')) {
            abort(403, 'You do not have the rights to update bid pricing group');
        }

        return response()->json(Group::create($request->validate(Group::$createRules)), 201);
    }

    public function update(Request $request, Group $group)
    {
        if (!Bouncer::can('edit-bidbond-pricing')) {
            abort(403, 'You do not have the rights to update bid pricing group');
        }
        $data = $request->validate(['name' => 'unique:groups,name,' . $group->id]);
        $default_groups = collect(['customer', 'agent']);
        if ($default_groups->contains($group->name)) {
            return response()->json(['error' => ['You cannot edit customer and agent default groups']], 422);
        }
        $group->update($data);
        return response()->json($group->refresh(), 200);
    }

    public function destroy(Group $group)
    {
        if (!Bouncer::can('edit-bidbond-pricing')) {
            abort(403, 'You do not have the rights to delete bid pricing group');
        }
        $default_groups = collect(['customer', 'agent']);
        if ($default_groups->contains($group->name)) {
            return response()->json(['error' => ['You cannot delete customer and agent default groups']], 422);
        }
        $group->delete();
        return response()->json(['message' => "Group deleted successfully"], 200);
    }

    public function assignAgent(Request $request)
    {
        $data = $request->validate([
            'agent_id' => 'bail|required|numeric|exists:agents,id',
            'group_id' => 'bail|required|numeric|exists:groups,id',
        ]);

        $agent = Agent::find($data['agent_id']);

        $agent->group_id = $data['group_id'];

        $agent->save();

        return response()->json($agent);
    }

}
