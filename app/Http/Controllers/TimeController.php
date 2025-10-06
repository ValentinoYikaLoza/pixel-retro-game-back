<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeController extends Controller
{
    private function formatTimeLeft(Carbon $now, Carbon $target)
    {
        $diffInSeconds = $now->diffInSeconds($target);

        if ($diffInSeconds >= 86400) { // más de un día
            return [
                'time' => $now->diffInDays($target),
                'unit' => 'DIAS'
            ];
        } elseif ($diffInSeconds >= 3600) { // más de una hora
            return [
                'time' => $now->diffInHours($target),
                'unit' => 'HORAS'
            ];
        } elseif ($diffInSeconds >= 60) { // más de un minuto
            return [
                'time' => $now->diffInMinutes($target),
                'unit' => 'MINUTOS'
            ];
        } else { // solo segundos
            return [
                'time' => $diffInSeconds,
                'unit' => 'SEGUNDOS'
            ];
        }
    }

    /**
     * Tiempo restante hasta la próxima medianoche.
     */
    public function getTimeLeftTillNextMidnight()
    {
        $now = Carbon::now('America/Lima');
        $nextMidnight = $now->copy()->addDay()->startOfDay();

        return $this->ok(
            "Tiempo restante hasta la próxima medianoche",
            $this->formatTimeLeft($now, $nextMidnight)
        );
    }

    /**
     * Tiempo restante hasta el próximo domingo a las 8:00 PM.
     */
    public function getTimeLeftTillNextSunday20()
    {
        $now = Carbon::now('America/Lima');
        $nextSundayAt20 = $now->copy()->next(Carbon::SUNDAY)->setTime(20, 0, 0);

        return $this->ok(
            "Tiempo restante hasta el próximo domingo a las 8:00 PM",
            $this->formatTimeLeft($now, $nextSundayAt20)
        );
    }

    /**
     * Tiempo restante hasta el fin de mes a medianoche.
     */
    public function getTimeLeftTillNextMonthEndMidnight()
    {
        $now = Carbon::now('America/Lima');
        $endOfMonthMidnight = $now->copy()->endOfMonth()->addDay()->startOfDay();

        return $this->ok(
            "Tiempo restante hasta el fin de mes a medianoche",
            $this->formatTimeLeft($now, $endOfMonthMidnight)
        );
    }
}
