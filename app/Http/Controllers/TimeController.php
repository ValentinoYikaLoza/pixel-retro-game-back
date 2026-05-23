<?php

namespace App\Http\Controllers;

use App\Services\TimeService;

class TimeController extends Controller
{
    public function __construct(private readonly TimeService $service) {}

    public function getTime()
    {
        return $this->ok('Fecha actual', [
            'time' => $this->service->nowIso(),
        ]);
    }
}
