<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LogoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Le middleware session.valid vérifie l'authentification
    }

    public function rules(): array
    {
        return [
            'all_sessions' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'all_sessions.boolean' => 'Le champ all_sessions doit être un booléen.',
        ];
    }
}
