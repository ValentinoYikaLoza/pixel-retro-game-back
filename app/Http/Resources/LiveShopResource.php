<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Item de la tienda de vidas. `type_id`: 1 = precio en USD, 2 = precio en monedas.
 */
class LiveShopResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => (int) $this->quantity,
            'price' => (float) $this->price,
            'type_id' => (int) $this->type_id,
        ];
    }
}
