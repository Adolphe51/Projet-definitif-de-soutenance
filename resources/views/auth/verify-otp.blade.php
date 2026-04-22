@extends('layouts.auth.app')

@section('content')

    @php
        $otpLength = (int) config('cyberguard.auth.otp.code_length', 8);
        $otpTtlSeconds = (int) config('cyberguard.auth.otp.ttl_minutes', 3) * 60;
        $resendDelaySeconds = (int) config('cyberguard.auth.otp.resend_delay_seconds', 180);
    @endphp

    <div>
        <form method="POST" action="{{ route('otp.verify') }}" class="auth-form otp-form"
            data-otp-length="{{ $otpLength }}"
            data-otp-seconds="{{ $otpTtlSeconds }}"
            data-resend-seconds="{{ $resendDelaySeconds }}">
            @csrf

            <!-- Email Display -->
            <div class="form-group">
                <label for="email" class="form-label">Compte vérifié</label>

                <input type="email" class="form-input" id="email" name="email"
                    value="{{ old('email', session('otp_email')) }}" readonly aria-readonly="true">
            </div>

            <!-- OTP Input -->
            <div class="form-group">
                <label for="code" class="form-label">Code de vérification à {{ $otpLength }} chiffres</label>
                <div class="otp-container" aria-label="Code OTP">
                    @for($i = 0; $i < $otpLength; $i++)
                        <input type="text" class="otp-input" inputmode="numeric" maxlength="1" autocomplete="one-time-code">
                    @endfor
                </div>
                <input type="hidden" name="code" id="otpCode">

            </div>

            <!-- OTP Timer -->
            <div class="otp-info" role="status" aria-live="polite">
                <span>Code valable pendant</span>
                <strong id="otpTimer" class="otp-timer">{{ gmdate('i:s', $otpTtlSeconds) }}</strong>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="auth-button">
                <div class="spinner"></div>
                <span>Authentifier et accéder au tableau de bord</span>
            </button>
        </form>

        <!-- Resend Form -->
        <div class="form-actions">
            <form method="POST" action="{{ route('otp.resend') }}" class="resend-form">
                @csrf
                <input type="hidden" name="email" value="{{ old('email', session('otp_email')) }}">
                <button id="resendBtn" class="auth-button secondary" disabled>
                    <span>Renvoyer le code dans <span id="resendTimer">{{ $resendDelaySeconds }}</span>s</span>
                </button>
            </form>

            <a href="{{ route('login') }}" class="form-link">
                Retour à la connexion
            </a>
        </div>
    </div>

@endsection
