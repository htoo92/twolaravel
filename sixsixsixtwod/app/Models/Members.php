<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Model\BetNumberForMember;

class Members extends Model
{
    use HasFactory;
    protected $table = 'members';

    public function userID()
    {
        return $this->belongsTo('App\Models\BetNumberForMember','id');
    }
} 
