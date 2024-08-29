<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointsCart extends Model
{
    use HasFactory;
    protected $fillable = [
        'quantity',
        'pointsProduct_id',
        'pointsOrders_id',
    ];
    protected $hidden=[
        'created_at',
        'updated_at',
    ];


    public function pointsProduct(){
        return $this->belongsTo(PointsProduct::class,'pointsProduct_id');
    }
    public function pointsOrder(){
        return $this->belongsTo(pointsOrder::class);
    }
}
