<?php

namespace App\Http\Requests\Streak;

use Illuminate\Foundation\Http\FormRequest;

class GetStreakRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer'],
            // Mes opcional 'Y-m' (UTC); por defecto el mes actual.
            'month' => ['nullable', 'string', 'date_format:Y-m'],
        ];
    }
}
