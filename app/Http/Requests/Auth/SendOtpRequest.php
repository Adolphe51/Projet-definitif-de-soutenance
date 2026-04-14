<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public — pas d'auth requise
    }

    public function rules(): array
    {
        $rule = app()->environment('production')
            ? 'email:rfc,dns'
            : 'email:rfc';

        return [
            'email' => [
                'required',
                $rule,
                'max:255'
            ],
            'password' => ['required', 'string', 'min:8', 'max:128',], // <- ajouté
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'      => 'L\'adresse email est obligatoire.',
            'email.email'         => 'L\'adresse email n\'est pas valide.',
            'password.required'   => 'Le mot de passe est obligatoire.',
            'password.min'        => 'Le mot de passe doit contenir au moins 8 caractères.',
        ];
    }
}
