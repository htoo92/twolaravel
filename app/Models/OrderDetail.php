<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;
    protected $table = 'order_details';
    protected $fillable = [
        'order_id',
        'amount',
        'pink_number',
    ];
    public function Orders()
    {
    return $this->hasMany('App\Models\Order','id');
    }
}
