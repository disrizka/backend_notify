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

    /**
     * Tentukan channel pengiriman.
     * Kita gunakan 'database' agar muncul di lonceng notifikasi aplikasi.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Data yang akan disimpan ke tabel 'notifications' di database.
     */
    // Ganti method toArray menjadi toDatabase di InternalNotification.php
    public function toDatabase(object $notifiable): array
    {
        return [
            'title'   => $this->details['title'],
            'message' => $this->details['message'],
            'type'    => $this->details['type'], 
            'time'    => now()->format('H:i'),
        ];
    }
}