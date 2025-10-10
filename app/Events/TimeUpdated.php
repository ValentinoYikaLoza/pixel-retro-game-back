<?php

namespace App\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TimeUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $timeList;

    public function __construct()
    {
        $now = Carbon::now('America/Lima');
        $nextMidnight = $now->copy()->addDay()->startOfDay();
        $nextSundayAt20 = $now->copy()->next(Carbon::SUNDAY)->setTime(20, 0, 0);
        $endOfMonthMidnight = $now->copy()->endOfMonth()->addDay()->startOfDay();

        $this->userId = 1;
        $this->timeList = [
            'timeLeftUntilNextDay' => $this->formatTimeLeft($now, $nextMidnight),
            'timeLeftUntilNextWeek' => $this->formatTimeLeft($now, $nextSundayAt20),
            'timeLeftUntilNextMonth' => $this->formatTimeLeft($now, $endOfMonthMidnight),
            'currentMonth' => Carbon::now('America/Lima')->month,
        ];
    }

    private function formatTimeLeft(Carbon $now, Carbon $target)
    {
        $diffInSeconds = $now->diffInSeconds($target);

        if ($diffInSeconds >= 86400) {
            return ['time' => $now->diffInDays($target), 'unit' => 'DIAS'];
        } elseif ($diffInSeconds >= 3600) {
            return ['time' => $now->diffInHours($target), 'unit' => 'HORAS'];
        } elseif ($diffInSeconds >= 60) {
            return ['time' => $now->diffInMinutes($target), 'unit' => 'MINUTOS'];
        } else {
            return ['time' => $diffInSeconds, 'unit' => 'SEGUNDOS'];
        }
    }

    public function broadcastOn()
    {
        // Canal descriptivo solo para tiempos
        return new Channel('system.time.' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'TimeUpdated';
    }

    public function broadcastWith()
    {
        return ['timeList' => $this->timeList];
    }
}
