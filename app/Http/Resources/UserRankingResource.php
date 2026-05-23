<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Fila del ranking: solo lo que la tabla de posiciones muestra.
 */
class UserRankingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'score' => $this->score,
            'times_ranked_first' => $this->times_ranked_first,
            'flag' => $this->flag,
        ];
    }
}
