<?php

namespace App\Http\Requests\Streak;

use Illuminate\Foundation\Http\FormRequest;

class BuyFreezeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer'],
        ];
    }
}
