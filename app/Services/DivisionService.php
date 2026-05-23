<?php

namespace App\Services;

use App\Events\DivisionUpdated;
use App\Exceptions\ApiException;
use App\Models\DivisionModel;
use App\Repositories\Contracts\DivisionRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Collection;

class DivisionService
{
    public function __construct(
        private readonly DivisionRepositoryInterface $divisions,
        private readonly UserRepositoryInterface $users,
    ) {}

    /**
     * @return Collection<int, DivisionModel>
     */
    public function list(): Collection
    {
        return $this->divisions->all();
    }

    /**
     * División actual del usuario; emite el evento de actualización.
     */
    public function currentForUser(int $userId): DivisionModel
    {
        $user = $this->users->findById($userId);

        if (!$user) {
            throw ApiException::notFound('Usuario no encontrado');
        }

        $division = $this->divisions->findById((int) $user->division_id);

        if (!$division) {
            throw ApiException::notFound('Division no encontrada');
        }

        broadcast(new DivisionUpdated($userId, $division))->toOthers();

        return $division;
    }
}
