<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Highlevelnumberlimit extends Model
{
    use HasFactory;
    protected $table = 'high_level_number_limit';
    // protected $cast = [
    //     'is_off'=>'array',
    // ];
    protected $fillable = [
        'numbers',
        'user_id',
        'amount',
    ];
    public function highlevelnumberlimits()
    {
        return $this->hasMany('App\Models\User','id');
    }
}
