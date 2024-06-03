<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassificationProduct extends Model
{
    use HasFactory;
    protected $fillable=[
        'classification_id',
        'product_id',
        'displayOrNot',
    ];
    protected $hidden=[
        'created_at',
        'updated_at',
    ];
    protected $table = 'classification_products';
        public function product(){
        return $this->belongsTo(Product::class,'product_id');
    }    

    public function carts(){
        return $this->hasMany(Cart::class);
    }
    public function classification(){
        return $this->belongsTo(Classification::class,'classification_id');
    }
}
