<?php

namespace App\Repositories\Contracts;

use App\Models\UserModel;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function findById(int $id): ?UserModel;

    /**
     * Ranking (top 20 por score) opcionalmente filtrado por división,
     * asegurando que el usuario solicitante aparezca en la lista.
     *
     * @return Collection<int, UserModel>
     */
    public function ranking(?int $divisionId, int $ensureUserId): Collection;

    public function save(UserModel $user): void;
}
