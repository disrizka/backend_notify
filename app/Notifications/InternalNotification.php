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

    public function via($notifiable)
    {
        return ['database']; 
    }

    public function toArray($notifiable)
    {
        return [
            'title'   => $this->data['title'],
            'message' => $this->data['message'],
            'type'    => $this->data['type'],
        ];
    }
}