<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointsOrder extends Model
{
    use HasFactory;
    protected $fillable =[
        'user_id',
        'pointsProduct_id',
        'quantity'
    ];

    protected $hidden=[
        'created_at',
        'updated_at',
    ];
    public function users(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function pointsProduct(){
        return $this->belongsTo(PointsProduct::class);
    }

}
