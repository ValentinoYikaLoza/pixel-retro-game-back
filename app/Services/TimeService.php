<?php

namespace App\Services;

use Carbon\Carbon;

class TimeService
{
    /**
     * Hora actual del servidor en UTC (ISO 8601 Zulu), ej: 2025-11-07T17:14:22Z.
     * Fuente de verdad: el cliente la adapta a la zona del usuario para mostrar
     * y la usa como instante para los countdowns globales.
     */
    public function nowIso(): string
    {
        return Carbon::now('UTC')->toIso8601ZuluString();
    }
}
