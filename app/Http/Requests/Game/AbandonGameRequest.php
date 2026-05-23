<?php

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

class AbandonGameRequest extends FormRequest
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
        ];
    }
}
