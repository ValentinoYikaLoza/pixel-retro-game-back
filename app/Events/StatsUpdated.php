<?php

namespace App\Events;

use App\Models\UserModel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StatsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;

    public function __construct(UserModel $user)
    {
        $this->user = [
            'id' => $user->id,
            'coins' => $user->coins,
            'lives' => $user->lives,
            'streak' => $user->streak
        ];
    }

    public function broadcastOn()
    {
        return new Channel('user.stats.' . $this->user['id']);
    }

    public function broadcastAs()
    {
        return 'StatsUpdated';
    }

    public function broadcastWith()
    {
        return [
            'user' => $this->user
        ];
    }
}
