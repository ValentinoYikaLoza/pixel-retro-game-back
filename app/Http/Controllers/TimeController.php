<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeController extends Controller
{
    public function getTime()
    {
        $now = Carbon::now('America/Lima');

        $response = [
            'time' => $now,
        ];

        return $this->ok("Fecha actual", $response);
    }
}
