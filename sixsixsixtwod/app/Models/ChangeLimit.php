<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChangeLimit extends Model
{
    use HasFactory;
    protected $table = 'change_limits';
    protected $fillable = [
        'limit_amount',
        'user_id',
    ];
    public function changeLimit()
    {
        return $this->hasOne('App\Models\ChangeLimit','id');
    }
}
 