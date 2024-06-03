<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintsAndSuggestion extends Model
{
    use HasFactory;
    protected $fillable =[
        'user_id',
        'type',
        'body',
    ];
    protected $hidden=[
        'created_at',
        'updated_at',
    ];
    public function users(){
        return $this->belongsTo(User::class);
    }
}
