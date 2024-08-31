<?php

namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use NotificationChannels\Fcm\FcmChannel;

class FirebasePushNotification extends Notification  implements ShouldQueue
{
    use Queueable;

    protected $title;
    protected $body;
    protected $data;

    public function __construct($title, $body, $data = [])
    {
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }


    public function toFcm($notifiable)
    {
        $deviceToken = $notifiable->routeNotificationFor('fcm');

        return CloudMessage::withTarget('token', $deviceToken)
            ->withNotification(FirebaseNotification::create($this->title, $this->body))
            ->withData($this->data);
    }
}
