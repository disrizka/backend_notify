<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InternalNotification extends Notification
{
    use Queueable;

    protected $details;

    /**
     * Create a new notification instance.
     * * @param array $details Harus berisi 'title', 'message', dan 'type'
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    // app/Notifications/InternalNotification.php

public function via($notifiable)
{
    return ['database']; // tetap simpan ke DB
}

public function toArray($notifiable)
{
    // Kirim FCM push jika user punya token
    if ($notifiable->fcm_token) {
        app(\App\Services\FcmPushService::class)->sendToToken(
            $notifiable->fcm_token,
            $this->details['title'],
            $this->details['message'],
            ['type' => $this->details['type']]
        );
    }

    return [
        'title'   => $this->details['title'],
        'message' => $this->details['message'],
        'type'    => $this->details['type'],
    ];
}
}