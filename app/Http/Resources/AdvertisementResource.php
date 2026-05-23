<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Anuncio recompensado. El cliente arma el texto desde `reward` + `rewardType`
 * (no se envía prosa). `rewardType ∈ {coin, life}`.
 */
class AdvertisementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reward' => (int) $this->reward,
            'rewardType' => $this->reward_type,
            // El seguimiento de reclamo por usuario es un siguiente paso.
            'is_claimed' => false,
        ];
    }
}
