<?php

namespace App\Services;

use Carbon\Carbon;

class TimeService
{
    private const TIMEZONE = 'America/Lima';

    /**
     * Hora actual del servidor en ISO 8601, ej: 2025-11-07T12:14:22.343-05:00.
     */
    public function nowIso(): string
    {
        return Carbon::now(self::TIMEZONE)->toIso8601String();
    }
}
