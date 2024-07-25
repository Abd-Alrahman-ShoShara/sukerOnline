<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointCart extends Model
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
        return $this->belongsTo(PointsProduct::class);
    }
    public function pointsOrder(){
        return $this->belongsTo(pointsOrder::class);
    }
}
