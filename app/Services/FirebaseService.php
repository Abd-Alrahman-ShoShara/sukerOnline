<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        // 🔹 اقرأ المسار من .env
        $serviceAccountPath = storage_path(env('FIREBASE_CREDENTIALS_PATH'));

        if (!file_exists($serviceAccountPath)) {
            throw new \Exception("Firebase service account file not found at: " . $serviceAccountPath);
        }

        $factory = (new Factory)->withServiceAccount($serviceAccountPath);
        $this->messaging = $factory->createMessaging();
    }

    public function sendNotification($deviceToken, $title, $body, $data = [])
    {
        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification(['title' => $title, 'body' => $body])
            ->withData($data);

        $this->messaging->send($message);
    }
}

// namespace App\Services;

// use Kreait\Firebase\Factory;
// use Kreait\Firebase\Messaging\CloudMessage;
// use Log;
// use Kreait\Firebase\Messaging\Notification;



// class FirebaseService
// {
//     protected $messaging;

//     public function __construct()
//     {
//         $seviceAccountPath= storage_path('sukeronline-122b5-firebase-adminsdk-a34cj-020b7ceff5.json');

//         $factory = (new Factory)->withServiceAccount($seviceAccountPath);
//         $this->messaging = $factory->createMessaging();
//     }

    // public function sendNotification($deviceToken, $title, $body,$data=[])
    // {
    //     $message = CloudMessage::withTarget('token',$deviceToken)->withNotification([ 'title'=> $title , 'body'=> $body ])->withData($data);
    //      $this->messaging->send($message);
    // }

    // public function sendNotification($deviceToken, $title, $body, $data = [])
    // {
    //     try {
    //         // نستخدم Notification بدلاً من مصفوفة
    //         $notification = Notification::create($title, $body);

    //         $message = CloudMessage::withTarget('token', $deviceToken)
    //             ->withNotification($notification)
    //             ->withData($data);

    //         $this->messaging->send($message);

    //         Log::info("✅ Notification sent successfully to token: {$deviceToken}");

    //     } catch (\Kreait\Firebase\Exception\Messaging\NotFound $e) {
    //         // في حال التوكن غير صالح أو محذوف
    //         Log::error("❌ Invalid/Expired token: {$deviceToken}. Error: ".$e->getMessage());

    //     } catch (\Kreait\Firebase\Exception\Messaging\InvalidMessage $e) {
    //         // في حال الرسالة غير صحيحة
    //         Log::error("❌ Invalid FCM message. Error: ".$e->getMessage());

    //     } catch (\Exception $e) {
    //         // أي خطأ آخر (شبكة، cURL، SSL)
    //         Log::error("🔥 FCM general error: ".$e->getMessage());
    //     }
    // }
// }