<?php

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

class FinishGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer'],
            'session_id' => ['required', 'integer'],
            'score' => ['required', 'integer', 'min:0'],
            'food_eaten' => ['required', 'integer', 'min:0'],
            'duration_ms' => ['required', 'integer', 'min:0'],
        ];
    }
}
