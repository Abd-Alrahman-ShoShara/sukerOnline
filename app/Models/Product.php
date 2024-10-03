<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable =[
        'name',
        'description',
        'price',
        'images',
        'type',
        'is_public',
        'points',
        'displayOrNot',
    ];

    protected $hidden=[
        'created_at',
        'updated_at',
    ];
    public function classificationProduct(){
        return $this->hasMany(ClassificationProduct::class,'classification_id');
    }
    public function cart(){
        return $this->hasMany(Cart::class);
    }
    public function classification()
    {
        return $this->belongsToMany(Classification::class, 'classification_products');
    }
    
}
