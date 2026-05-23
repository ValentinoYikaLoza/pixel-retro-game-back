<?php

namespace App\Repositories\Contracts;

use App\Models\GameModel;
use Illuminate\Support\Collection;

interface GameRepositoryInterface
{
    /**
     * Catálogo de juegos.
     *
     * @return Collection<int, GameModel>
     */
    public function all(): Collection;

    /** Juego por su `code` (columna `name`), con la config de partida. */
    public function findByCode(string $code): ?GameModel;

    /** Juego por id, con la config de partida. */
    public function findById(int $id): ?GameModel;
}
