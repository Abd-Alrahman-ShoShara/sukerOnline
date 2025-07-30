<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public $timestamps = false;          // لا نحتاج created_at / updated_at
    public $incrementing = false;        // لأن الـ primary key نصي
    protected $primaryKey = 'key';
    protected $keyType   = 'string';

    protected $fillable = ['key', 'value'];

    // تلقائيًا حوِّل JSON المحفوظ في value إلى Array والعكس
    protected $casts = [
        'value' => 'json',
    ];
}
