<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;
    protected $table = 'members';
    public function orders()
    {
    return $this->belongTo('App\Models\Order','id');
    }
    public function myOrders()
    {
    return $this->hasMany('App\Models\Order','id');
    }
    public function betNumberForMembers()
    {
    return $this->hasMany('App\Models\BetNumberForMember','id');
    }

    public function memberUser(){
        return $this->hasMany('App\Models\User','id');
    }
}
