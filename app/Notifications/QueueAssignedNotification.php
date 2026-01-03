<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class QueueAssignedNotification extends Notification
{
    use Queueable;

    protected $detectionId;
    protected $customerId;

    public function __construct($detectionId, $customerId)
    {
        $this->detectionId = $detectionId;
        $this->customerId = $customerId;
    }

    public function via($notifiable)
    {
        // store in database and broadcast (if configured)
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Antrian Anda telah dibuat oleh petugas keamanan',
            'customer_id' => $this->customerId,
            'detection_id' => $this->detectionId,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
