<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Misión asignada a un usuario (sirve para daily/weekly/monthly: comparten forma).
 */
class MissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'current_value' => $this->current_value,
            'description' => $this->description,
            'total_value' => $this->total_value,
            'reward_id' => $this->reward_id,
            'status_id' => $this->status_id,
        ];
    }
}
