<?php

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

class StartGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer'],
            'game_code' => ['required', 'string'],
            // level 0 = modo infinito (sin nivel/objetivo); >=1 = nivel normal.
            'level' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
