<?php

namespace App\Models;

use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use APP\Models\ChangeLimit;
use App\Models\Bet;
use App\Models\BetNumberForMember;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'group_id',
        'ownerdetails_overrate',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    protected $primaryKey = 'id';
    public function groups()
    {
        return $this->hasMany('App\Models\Group','id');
    }

    public function change_limits()
    {
        return $this->belongsTo('ChangeLimit','id');    
    }

    public function bets()
    {
        return $this->belongsTo('Bet','id');
    }

    public function betNumberUserID()
    {
        return $this->belongsTo('BetNumberForMember','id');
    }

    

    
}
