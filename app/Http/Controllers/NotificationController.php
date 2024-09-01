<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;

 class NotificationController extends Controller
{
    public $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function sendPushNotification($deviceToken,$title,$body,$data)
    {
        $this->firebaseService->sendNotification($deviceToken,$title,$body,$data);
       
    }
}
