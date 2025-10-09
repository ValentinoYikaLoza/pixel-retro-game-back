<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserMissionsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $missions;

    public function __construct(int $userId, array $missions)
    {
        $this->userId = $userId;
        $this->missions = [
            'dailyMissions' => $missions['dailyMissions'] ?? [],
            'weeklyMissions' => $missions['weeklyMissions'] ?? [],
            'monthlyMissions' => $missions['monthlyMissions'] ?? [],
        ];
    }

    public function broadcastOn()
    {
        return new Channel('user.missions.' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'UserMissionsUpdated';
    }

    public function broadcastWith()
    {
        return [
            'missions' => $this->missions,
        ];
    }
}
