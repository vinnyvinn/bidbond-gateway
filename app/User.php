<?php

namespace App;

use DateTimeInterface;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Silber\Bouncer\Database\HasRolesAndAbilities;
use App\Traits\AddsUnique;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, HasRolesAndAbilities, AddsUnique, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    protected $table = 'users';
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'deleted_at', 'signup_platform'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'active' => 'boolean',
        'requires_password_change' => 'boolean',
        'verified_otp' => 'boolean',
        'verified_phone' => 'boolean',
        'create_by_admin' => 'boolean'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'assigned_roles', 'entity_id', 'role_id');
    }

    public function agent()
    {
        return $this->belongsToMany(Agent::class, 'agent_user')->withTimestamps();
    }

    //user can create many agents
    public function agents()
    {
     return $this->hasMany(Agent::class, 'created_at', 'id');
    }

    public function commission()
    {
        return $this->hasMany(RowCommision::class);
    }

    public function scopeWhereActive($builder, $boolean = true): void
    {
        $builder->where('active', $boolean);
    }

    public function scopeEmailVerified($builder): void
    {
        $builder->whereNotNull('email_verified_at');
    }


    public function scopeWhereRequiresPasswordChange($builder, $boolean = true): void
    {
        $builder->where('requires_password_change', $boolean);
    }

    public function scopeSearch($query, string $terms = null)
    {
        collect(str_getcsv($terms, ' ', '"'))->filter()->each(function ($term) use ($query) {
            $term = $term . '%';
            $query->where(function ($query) use ($term) {
                $query->where('firstname', 'like', $term)
                    ->orwhere('lastname', 'like', $term)
                    ->orwhere('middlename', 'like', $term)
                    ->orwhere('email', 'like', $term)
                    ->orwhere('id_number', 'like', $term);
            });
        });
    }


}
