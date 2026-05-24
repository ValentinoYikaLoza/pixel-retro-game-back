<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Stats de un usuario (lectura individual).
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'coins' => $this->coins,
            'lives' => $this->lives,
            'score' => $this->score,
            'streak' => $this->streak,
            // Solo presente tras el check-in diario (getUser): true si la racha
            // subió hoy, para que el cliente muestre el toast de "+1".
            'streak_incremented' => (bool) ($this->streak_incremented ?? false),
            'division_id' => $this->division_id,
        ];
    }
}
