<?php

namespace App\Repositories\Contracts;

use App\Models\UserModel;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function findById(int $id): ?UserModel;

    /**
     * Igual que findById pero con bloqueo de fila (SELECT ... FOR UPDATE). Debe
     * usarse dentro de una transacción para serializar lecturas-modificación-
     * escritura sobre los stats del usuario y evitar lost updates concurrentes.
     */
    public function findForUpdate(int $id): ?UserModel;

    /**
     * Ranking (top 20 por score) opcionalmente filtrado por división,
     * asegurando que el usuario solicitante aparezca en la lista.
     *
     * @return Collection<int, UserModel>
     */
    public function ranking(?int $divisionId, int $ensureUserId): Collection;

    public function save(UserModel $user): void;
}
