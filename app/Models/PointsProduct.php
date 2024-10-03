<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PointsProduct extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable =[
        'name',
        'description',
        'price',
        'images',
        'quantity',
        'points',
        'displayOrNot',
    ];

    protected $hidden=[
        'created_at',
        'updated_at',
    ];
    
}
