<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Juego del catálogo. `code` es la clave estable que el cliente usa para el
 * asset y la ruta (no se exponen nombres de archivo del cliente).
 */
class GameResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->name,
            'title' => $this->title,
            'enabled' => (bool) $this->enabled,
        ];
    }
}
