<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoredOrder extends Model
{
    protected $fillable = [
        'order_id',
        'storingTime',
    ];
    use HasFactory;
}
