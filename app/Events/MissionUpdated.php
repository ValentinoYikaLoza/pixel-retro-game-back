<?php

namespace App\Events;

use App\Models\UserMissionModel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MissionUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $mission;

    public function __construct(UserMissionModel $mission)
    {
        $this->mission = $mission->load('mission');
    }

    public function broadcastOn()
    {
        // Canal privado por usuario
        return new Channel("missions.{$this->mission->user_id}");
    }

    public function broadcastAs()
    {
        return 'mission.progress';
    }
}
