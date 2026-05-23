<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Una fila del leaderboard por juego: mejor puntaje de un usuario en ese juego.
 */
class GameLeaderboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user_id' => (int) $this->user_id,
            'name' => $this->name,
            'high_score' => (int) $this->high_score,
            'total_games' => (int) $this->total_games,
            'flag' => $this->flag,
        ];
    }
}
