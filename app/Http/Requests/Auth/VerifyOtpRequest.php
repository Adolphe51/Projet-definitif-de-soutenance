<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
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
                'max:255',
            ],
            'code' => [
                'required',
                'string',
                'regex:/^\d{8}$/', // exactement 8 chiffres
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'code.required' => 'Le code OTP est obligatoire.',
            'code.regex' => 'Le code doit contenir exactement 8 chiffres.',
        ];
    }
}
