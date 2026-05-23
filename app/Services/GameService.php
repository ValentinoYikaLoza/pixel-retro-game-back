<?php

namespace App\Services;

use App\Repositories\Contracts\GameRepositoryInterface;
use Illuminate\Support\Collection;

class GameService
{
    public function __construct(private readonly GameRepositoryInterface $games) {}

    public function list(): Collection
    {
        return $this->games->all();
    }
}
