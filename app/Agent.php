<?php

namespace App;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Agent extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $hidden = ['deleted_at'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->secret = $model->saveSecret();
        });
    }

    protected function saveSecret()
    {
        do {
            $secret = getToken('5');
        } while (Agent::secret($secret)->count() > 0);

        return $secret;
    }


    public function scopeSecret($builder, $secret):void
    {
        $builder->where('secret', $secret);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by', 'id');
    }

    public function users()
    {
        return $this->belongsToMany('App\User', 'agent_user')->withTimestamps();
    }

    public function delete()
    {
        DB::transaction(function () {
            $this->users()->delete();
            parent::delete();
        });
    }
}
