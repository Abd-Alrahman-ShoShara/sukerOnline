<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointsProduct extends Model
{
    use HasFactory;
    protected $fillable =[
        'name',
        'description',
        'price',
        'images',
        'number',
        'points',
        'displayOrNot',
    ];

    protected $hidden=[
        'created_at',
        'updated_at',
    ];
    
}
