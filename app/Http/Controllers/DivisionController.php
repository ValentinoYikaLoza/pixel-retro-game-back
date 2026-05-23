<?php

namespace App\Http\Controllers;

use App\Http\Resources\DivisionResource;
use App\Services\DivisionService;

class DivisionController extends Controller
{
    public function __construct(private readonly DivisionService $service) {}

    public function index()
    {
        return $this->ok(
            'Listado de Divisiones',
            DivisionResource::collection($this->service->list()),
        );
    }

    public function current(int $userId)
    {
        return $this->ok(
            'Division actual',
            new DivisionResource($this->service->currentForUser($userId)),
        );
    }
}
