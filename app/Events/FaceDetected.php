<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use App\Models\FaceDetection;

class FaceDetected implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public FaceDetection $detection;

    public function __construct(FaceDetection $detection)
    {
        $this->detection = $detection;
    }

    public function broadcastOn()
    {
        return new Channel('security');
    }

    public function broadcastWith()
    {
        return ['detection' => $this->detection->toArray()];
    }
}
