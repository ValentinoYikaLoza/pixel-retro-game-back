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
            'division_id' => $this->division_id,
        ];
    }
}
