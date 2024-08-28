<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'state',
        'type',
        'points',
    ];
    protected $hidden=[
        
        'updated_at',
    ];
    public function users(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function carts(){
        return $this->hasMany(Cart::class);
    }
    public function storedOrders(){
        return $this->hasOne(StoredOrder::class,'order_id');
    }
}
