<?php

namespace App\Http\Requests\Mission;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer'],
            'mission_type' => ['required', 'string', 'in:daily,weekly,monthly'],
            'mission_id' => ['required', 'integer'],
            'progress' => ['required', 'integer'],
        ];
    }
}
