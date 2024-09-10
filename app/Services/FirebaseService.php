<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;


class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        $seviceAccountPath= storage_path('sukeronline-122b5-firebase-adminsdk-a34cj-020b7ceff5.json');

        $factory = (new Factory)->withServiceAccount($seviceAccountPath);
        $this->messaging = $factory->createMessaging();
    }

    public function sendNotification($deviceToken, $title, $body,$data=[])
    {
        $message = CloudMessage::withTarget('token',$deviceToken)->withNotification([ 'title'=> $title , 'body'=> $body ])->withData($data);
         $this->messaging->send($message);
    }
}