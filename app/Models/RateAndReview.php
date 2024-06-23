<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateAndReview extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'rate',
        'rateBody',
    ];
    protected $hidden=[
        'created_at',
        'updated_at',
    ];
    public function users(){
        return $this->belongsTo(User::class,'user_id');
    }

    
}
