<?php

namespace App\Events;

use App\Models\DivisionModel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UsersUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $users;

    public function __construct(int $userId, array $users)
    {
        $this->userId = $userId;
        $this->users = [
            'userList' => $users['users'] ?? [],
        ];
    }

    public function broadcastOn()
    {
        return new Channel('user.users.' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'UsersUpdated';
    }

    public function broadcastWith()
    {
        return [
            'users' => $this->users,
        ];
    }
}
