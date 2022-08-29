<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Members;
use App\Models\Order;
use App\Models\NumberType;

class BetNumberForMember extends Model
{
    use HasFactory;
    protected $table = 'bet_numbers_for_members';
    public function userID()
    {
        return $this->hasMany('App\Models\User','id');
    }
    public function memberID()
    {
        return $this->hasMany('App\Models\Members','id'); 
    }
    public function luckymember(){
        return $this->belongsTo(Member::class,'member_id');
    }
    public function mynum(){
        return $this->belongsTo(NumberType::class,'number_type');
    }
    public function owner(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function myorders(){
        return $this->belongsTo(Order::class,'member_id');
    }
    public function luckymemberpercentage(){
        return $this->belongsTo(User::class,'user_id');
    }
} 
