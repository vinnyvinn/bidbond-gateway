<?php

namespace App\Jobs;

use App\BidbondPrice;
use App\Commission;
use App\RowCommision;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SetCommission implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bidbond;
    protected $company;

    /**
     * Create a new job instance.
     *
     * @param $bidbond
     * @param $company
     */
    public function __construct($bidbond, $company)
    {
        $this->bidbond = $bidbond;
        $this->company = $company;
    }

    /**
     * Execute the job.
     *
     *
     * @return void
     */
    public function handle()
    {
        if ($this->bidbond->agent_id) return;
        if (!$this->company->relationship_manager_id) return;

        $user = User::find($this->company->relationship_manager_id);

        if (!$user) return;

        $role = $user->roles()->first();
        $commision = Commission::role($role->id)->active()->first();

        if (!$commision) return;

        $bid_ref = $this->bidbond->secret;
        $bid_bond_charge = BidbondPrice::getBreakdown($this->bidbond->amount, $this->bidbond->period, $this->company->group_id)["bid_bond_charge"];
        $commision_amount = bcmul($commision->amount, $bid_bond_charge) / 100;
        $row_commission = RowCommision::ofUser($user->id)->bidbond($bid_ref)->first();

        if (!$row_commission) {
            RowCommision::create([
                'user_id' => $user->id,
                'commission_amount' => $commision_amount,
                'commission_type' => 'Bidbond',
                'bidbond_id' => $bid_ref
            ]);
        }
    }
}
