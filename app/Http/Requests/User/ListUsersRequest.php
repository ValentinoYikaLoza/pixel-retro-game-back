<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ListUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer'],
            // El frontend acota el ranking; el backend devuelve el top de la división.
            'limit' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
