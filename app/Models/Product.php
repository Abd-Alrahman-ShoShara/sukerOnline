<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable =[
        'name',
        'description',
        'price',
        'images',
        'type',
        'is_public',
    ];

    protected $hidden=[
        'created_at',
        'updated_at',
    ];
    public function classificationProduct(){
        return $this->hasMany(ClassificationProduct::class,'classification_id');
    }
    public function classification()
    {
        return $this->belongsToMany(Classification::class, 'classification_products');
    }
    
}
