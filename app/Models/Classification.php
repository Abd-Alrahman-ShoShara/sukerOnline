<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classification extends Model
{
    use HasFactory;
    protected $fillable=[
        'name',
        
    ];
    protected $hidden=[
        'created_at',
        'updated_at',
    ];
    
    public function user(){
        return $this->hasMany(User::class);
    }
    public function classificationProduct(){
        return $this->hasMany(ClassificationProduct::class);
    }
    

    public function products()
    {
        return $this->belongsToMany(Product::class, 'classification_products');
    }
}
