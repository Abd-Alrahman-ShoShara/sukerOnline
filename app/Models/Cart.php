<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $fillable =[
        'order_id',
        'product_id',
        'quantity',
    ];
    protected $hidden=[
        'created_at',
        'updated_at',
    ];

    public function classificationProducts(){
        return $this->belongsTo(ClassificationProduct::class);
    }
    public function order(){
        return $this->belongsTo(Order::class);
    }
}
