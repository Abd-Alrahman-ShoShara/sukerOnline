<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'password',
        'role',
        'classification_id',
        'adress',
        'nameOfStore',
        'userPoints',
        'fcm_token',
        
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function order(){
        return $this->hasMany(Order::class);
    }
    public function pointsOrder(){
        return $this->hasMany(PointsOrder::class);
    }

    public function complaints(){
        return $this->hasMany(ComplaintsAndSuggestion::class);
    }
    public function rateAndReview(){
        return $this->hasMany(RateAndReview::class);
    }
    public function classification(){
        return $this->belongsTo(Classification::class,'classification_id');
    }
    public function routeNotificationForFcm()
{
    return $this->fcm_token; 
}

}
