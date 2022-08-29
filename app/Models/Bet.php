<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    use HasFactory;
    protected $table = 'betnumbers';
    protected $fillable = [
        'number',
        'user_id',
        'amount',
        'over_amount',
        'is_over',
        'to_leader',
        'to_supervisor',
    ];
    public function users()
    {
        return $this->hasMany('App\Models\User','id');
    }
}
