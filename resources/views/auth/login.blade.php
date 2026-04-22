@extends('layouts.auth.app')

@section('content')

    <div>
        <form method="POST" action="{{ route('otp.send') }}" class="auth-form">
            @csrf

            <!-- Email Field -->
            <div class="form-group">
                <label for="email" class="form-label">Adresse email professionnelle</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="administrateur@entreprise.com"
                    value="{{ old('email') }}" autocomplete="email" required autofocus>
                <p class="form-help">
                    Un code de vérification à 8 chiffres sera envoyé à cette adresse email.
                </p>
            </div>

            <!-- Password Field -->
            <div class="form-group">
                <label for="password" class="form-label">Mot de passe administrateur</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="••••••••••••"
                    autocomplete="current-password" required minlength="8">
            </div>

            <!-- Submit Button -->
            <button type="submit" class="auth-button">
                <div class="spinner"></div>
                <span>Continuer vers la vérification</span>
            </button>
        </form>
    </div>

@endsection