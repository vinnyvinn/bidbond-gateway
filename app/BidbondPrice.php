<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
class BidbondPrice extends Model
{

    protected $guarded = [];

    const TYPES = [
        'fixed' => 'fixed',
        'percent' => 'percent'
    ];

    public static $createRules = [
        'group_id' => 'bail|required|exists:groups,id',
        'lower_bound' => 'required|numeric|gte:0|lt:upper_bound',
        'upper_bound' => 'required|numeric|gt:lower_bound',
        'cost' => 'bail|required|numeric',
        'duration' => 'required|numeric',
        'charge_type' => 'bail|required|in:fixed,percent',
        'discount' => 'required|numeric',
        'discount_type' => 'bail|required|in:fixed,percent',
    ];

    public static $updateRules = [
        'lower_bound' => 'required|numeric|gte:0|lt:upper_bound',
        'upper_bound' => 'required|numeric|gt:lower_bound',
        'cost' => 'bail|required|numeric',
        'duration' => 'required|numeric',
        'charge_type' => 'bail|required|in:fixed,percent',
        'discount' => 'required|numeric',
        'discount_type' => 'bail|required|in:fixed,percent',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public static function calculateBidBondCharge($amount, $period, $group_id = 1)
    {
        $pricing = self::where('group_id', $group_id)
            ->where('lower_bound', '<=', $amount)
            ->where('upper_bound', '>=', $amount)
            ->first();

        if ($pricing) {

            if ($pricing->charge_type == 'fixed') {
                return ($pricing->cost - self::_getDiscount($pricing)) * ceil($period / $pricing->duration);
            } else {
                return ((($pricing->cost / 100) * $amount) - self::_getDiscount($pricing, $amount)) * ceil($period / $pricing->duration);
            }
        }

        return null;
    }

    public static function getBreakdown($amount, $period, $group_id): array
    {
        $period = $period / 30;

        $bid_bond_charge = self::calculateBidBondCharge($amount, $period, $group_id);

        $excise_duty = Cache::rememberForever(PriceSetting::excise_duty, function () {
            return PriceSetting::option(PriceSetting::excise_duty)->first()->value;
        });

        $excise_charge = $excise_duty * $bid_bond_charge;

        $indemnity_cost = Cache::rememberForever(PriceSetting::indemnity_cost, function () {
            return PriceSetting::option(PriceSetting::indemnity_cost)->first()->value;
        });

        return [
            'total' => round($bid_bond_charge + $excise_charge + $indemnity_cost),
            'excise_duty' => round($excise_charge),
            'bid_bond_charge' => round($bid_bond_charge),
            'indemnity_cost' => round($indemnity_cost)
        ];
    }

    protected static function _getDiscount($pricing, $amount = null)
    {
        if ($pricing->discount_type == 'fixed') {
            return $pricing->discount;
        }

        if ($pricing->charge_type == 'fixed') {
            return $pricing->discount / 100 * $pricing->cost;
        }

        return $pricing->discount / 100 * $pricing->cost / 100 * $amount;
    }

}
