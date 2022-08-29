<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table = 'orders';
    protected $fillable = [
        'voucher_number',
        'member_id',
    ];
    public function members()
    {
    return $this->hasMany('App\Models\Member','id');
    }

    public function orderDetail(){
        return $this->hasMany('App\Models\OrderDetail','id');
    }
    public function orderDetails()
    {
    return $this->belongTo('App\Models\OrderDetail','id');
    }
}
