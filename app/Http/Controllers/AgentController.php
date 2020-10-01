<?php

namespace App\Http\Controllers;

use App\Services\BidBondService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Jobs\NotifyUser;
use App\Agent;
use App\User;
use App\Business;
use Bouncer;


class AgentController extends Controller
{
    use ApiResponser;

    public $bidBondService;

    public function __construct(BidBondService $bidBondService)
    {
        $this->bidBondService = $bidBondService;
    }

    public function index()
    {
        if (Bouncer::can('list-agents-owned')) {
            $user = auth()->user();
            $user_agents = $user->agent()->get();
            $agents = Agent::where('created_by', $user->id)->get();
            $merged = $agents->merge($user_agents)->unique();
            $agents = [
                "data" => $merged->values()->all(),
                "from" => 1
            ];

        } else if (Bouncer::can('list-agents')) {
            $agents = Agent::paginate();
        }
        return $this->successResponse($agents);
    }

    public function store(Request $request)
    {
        if (Bouncer::cannot('onboard-agencies')) {
            $this->errorResponse('You do not have the right create an agent', 403);
        }

        $data = [
            'firstname' => 'bail|required|max:191',
            'lastname' => 'bail|required|max:191',
            'id_number' => 'bail|required|numeric|unique:users,id_number',
            'phone' => 'bail|required|digits_between:10,15|unique:users,phone_number,users,|unique:agents,phone',
            'email' => 'bail|required|email|max:191|unique:users,email',
            'agent_type' => 'required|in:individual,business',
            'limit' => 'bail|required|numeric',
            'group_id' => 'bail|required|exists:groups,id',
            'customerid' => 'bail|required|max:191',
            'account' => 'bail|required|max:191'
        ];

        if ($request->input('agent_type') == "business") {
            $data = array_merge(
                $data,
                [
                    'business_name' => 'required|max:191',
                    'business_phone' => 'required|digits_between:10,15',
                    'business_email' => 'required|email|max:191',
                    'business_physical_address' => 'required|max:191',
                    'business_postal_address' => 'required|numeric',
                    'business_postal_code_id' => 'required',
                    'crp' => 'required|unique:agents,crp|max:191',
                ]
            );
        } else {
            $data = array_merge(
                $data,
                [
                    'physical_address' => 'required|max:191',
                    'postal_address' => 'required|numeric',
                    'postal_code_id' => 'required',
                ]
            );
        }
        $this->validate($request, $data);
        $auth_user = auth()->user();

        if ($request->input('agent_type') == "business") {
            $data = [
                "name" => $request->business_name,
                "email" => $request->business_email,
                "phone" => $request->business_phone,
                "physical_address" => $request->business_physical_address,
                "postal_address" => $request->business_postal_address,
                "postal_code_id" => $request->business_postal_code_id,
                "agent_type" => $request->agent_type,
                "created_by" => $auth_user->id,
                "crp" => $request->crp
            ];
        } else {
            $data = [
                "name" => $request->firstname . " " . $request->lastname,
                "email" => $request->email,
                "phone" => $request->phone,
                "physical_address" => $request->physical_address,
                "postal_address" => $request->postal_address,
                "postal_code_id" => $request->postal_code_id,
                "agent_type" => $request->agent_type,
                "created_by" => $auth_user->id
            ];
        }
        $data = array_merge($data, [
            "limit" => $request->limit,
            "balance" => $request->limit,
            "customerid" => $request->customerid,
            "account" => $request->account,
            "group_id" => $request->group_id
        ]);
        $agent = Agent::create($data);
        $user = $this->createAgentUserAccount($request);
        $user->allow('update-agents-owned');
        $agent->refresh();
        $agent->users()->sync($user);
        $this->bidBondService->setAgentLimits(['agent_id' => $agent->secret, 'limit' => $request->limit]);

        return response()->json($agent, 201);
    }

    public function show(Agent $agent)
    {
        $users = $agent->users()->get();
        $agent = $agent->load('group');
        return response()->json(["agent" => $agent, "users" => $users], 200);
    }

    public function update(Request $request, Agent $agent)
    {
        if (!Bouncer::can('update-agent')) {
            $this->errorResponse('You do not have the rights to update agents', 403);
        }
        $this->validate($request, [
            'name' => 'required|max:191',
            'phone' => 'bail|required|digits_between:10,15|unique:agents,phone,' . $agent->id,
            'email' => 'bail|required|email|max:191|unique:agents,email,' . $agent->id,
            'agent_type' => 'required|in:individual,business',
            'physical_address' => 'required|max:191',
            'postal_address' => 'required|numeric',
            'postal_code_id' => 'required',
            'group_id' => 'required|numeric',
            'limit' => 'bail|required|numeric',
            'customerid' => 'bail|required|max:191',
            'account' => 'bail|required|max:191'
        ]);
        if ($request->agent_type == "business") {
            $this->validate($request, [
                'crp' => 'unique:agents,crp,' . $agent->id . '|max:191',
            ]);
        }
        //update balance by difference
        if ($agent->limit > $request->limit) {
            $diff = $agent->limit - $request->limit;
            $agent->balance = $agent->balance - $diff;
        } else {
            $diff = $request->limit - $agent->limit;
            $agent->balance = $agent->balance + $diff;
        }
        $agent->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'agent_type' => $request->input('agent_type'),
            'postal_address' => $request->input('postal_address'),
            'physical_address' => $request->input('physical_address'),
            'postal_code_id' => $request->input('postal_code_id'),
            'crp' => $request->input('crp'),
            'limit' => $request->limit,
            'balance' => $agent->balance,
            'group_id' => $request->group_id,
            'customerid' => $request->customerid,
            'account' => $request->account
        ]);
        $this->bidBondService->updateAgentLimits(['agent_id' => $agent->secret, 'limit' => $request->limit]);
        return response()->json(["message" => "Agent updated successfully"]);
    }

    public function destroy(Agent $agent)
    {
        if (Bouncer::cannot('delete-agencies')) {
            $this->errorResponse('You do not have the delete an agent', 403);
        }
        $agent->delete();
        return response()->json(["message" => "Agent deleted"]);
    }

    public function restore($id)
    {
        $user = Agent::onlyTrashed()->findOrFail($id);
        $user->restore();
        return $this->successResponse($user);
    }

    public function listDeleted()
    {
        $query = Agent::onlyTrashed();
        $resource = $query->paginate();
        $resource->setCollection($resource->getCollection()->makeVisible('deleted_at'));
        return $this->successResponse($resource);
    }

    public function options()
    {
        if (Bouncer::can('list-agents-owned')) {
            $user = auth()->user();
            $user_agents = $user->agent()->select('agents.id', 'name', 'secret')->get();
            $agents = Agent::select('id', 'name', 'secret')->where('created_by', $user->id)->get();
            $agents = $agents->merge($user_agents)->unique()->values()->all();
        } else if (Bouncer::can('list-agents')) {
            $agents = Agent::select('id', 'name')->get();
        }
        return $this->successResponse($agents);
    }

    protected function createAgentUserAccount(Request $request): User
    {
        $loggedInuser = auth()->user();
        $pass = getToken();
        $user = User::create([
            "firstname" => $request->firstname,
            "lastname" => $request->lastname,
            "id_number" => $request->id_number,
            "email" => $request->email,
            "phone_number" => $request->phone,
            "password" => Hash::make($pass),
            "verified_phone" => true,
            "verified_otp" => true,
            "email_verified_at" => now(),
            "requires_password_change" => true,
            "parent" => $loggedInuser->id
        ]);
        $user->refresh();
        $user->assign("agent");
        $creator = $loggedInuser->firstname . " " . $loggedInuser->lastname;
        NotifyUser::dispatchNow($user, $creator, $pass);
        return $user;
    }

    public function linkUser(Request $request, $id)
    {
        if (Bouncer::can('update-agent') || Bouncer::can('update-agents-owned')) {
            $agent = Agent::findOrFail($id);
            $this->validate($request, [
                'firstname' => 'bail|required|max:191',
                'lastname' => 'bail|required|max:191',
                'id_number' => 'bail|required|numeric|unique:users,id_number',
                'phone' => 'bail|required|digits_between:10,15|unique:users,phone_number,users,|unique:agents,phone',
                'email' => 'bail|required|email|max:191|unique:users,email'
            ]);
            $loggedInUser = auth()->user();
            if ($loggedInUser->isAn('agent') && !$agent->users()->exists($loggedInUser)) {
                abort(403, 'You do not have the right permissions to link a user to this agency');
            }
            $user = $this->createAgentUserAccount($request);
            $agent->users()->attach($user);

            return response()->json($agent, 201);
        }
        abort(403, "You do not have the permissions to create an agent user");
    }

    public function unlinkUser(Request $request, $id)
    {
        if (Bouncer::can('update-agent') || Bouncer::can('update-agents-owned')) {
            $agent = Agent::findOrFail($id);
            $this->validate($request, [
                'user_id' => 'bail|required|numeric|exists:agent_user,user_id',
            ]);
            $loggedInUser = auth()->user();
            if ($loggedInUser->isAn('agent') && !$agent->users()->exists($loggedInUser)) {
                abort(403, 'You do not have the right permissions to link a user to this agency');
            }
            if ($loggedInUser->id == $request->user_id) {
                abort(403, "You cannot delete your own account");
            }
            User::find($request->user_id)->delete();
            return response()->json($agent, 201);
        }
        abort(403, "You do not have the permissions to unlink an agent");
    }

    public function restoreLimit(Request $request)
    {
        $request->validate([
            '*.agent_id' => 'bail|required|exists:agents,secret',
            '*.amount' => 'bail|required|numeric'
        ]);

        foreach ($request->all() as $item) {
            $agent = Agent::secret($item['agent_id'])->firstOrFail();
            $agent->update(['balance' => bcadd($agent->balance, $item['amount'])]);
        }

        return response()->json("Balance updated successfully");
    }

    public function increaseBidUsage(Request $request)
    {
        $request->validate([
            'agent_id' => 'bail|required|exists:agents,secret',
            'amount' => 'bail|required|numeric'
        ]);

        $agent = Agent::secret($request->agent_id)->firstOrFail();

        $agent->balance = bcsub($agent->balance, $request->amount);

        $agent->save();

        return response()->json("Balance updated successfully");
    }

}
