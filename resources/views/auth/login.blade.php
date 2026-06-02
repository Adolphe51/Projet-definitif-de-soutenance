@extends('layouts.auth.app')

@section('content')

    <div>
        <form method="POST" action="{{ route('otp.send') }}" class="auth-form">
            @csrf

            <!-- Email Field -->
            <div class="form-group">
                <label for="email" class="form-label">Adresse email du compte</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="compte@entreprise.com"
                    value="{{ old('email') }}" autocomplete="username" inputmode="email" autocapitalize="none"
                    spellcheck="false" aria-describedby="email-help" required autofocus>
                <p class="form-help" id="email-help">
                    Le même écran permet d’ouvrir soit CyberGuard admin, soit le mini site métier selon le compte utilisé.
                </p>
            </div>

            <!-- Password Field -->
            <div class="form-group">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="••••••••••••"
                    autocomplete="current-password" autocapitalize="none" spellcheck="false" required minlength="8">
            </div>

            <!-- Submit Button -->
            <button type="submit" class="auth-button">
                <div class="spinner"></div>
                <span>Continuer vers la vérification</span>
            </button>
        </form>
    </div>

@endsection
