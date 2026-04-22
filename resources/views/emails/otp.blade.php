@extends('layouts.auth.app')

@section('title', 'OTP')

@section('content')
<p>Bonjour {{ $authCode->user->nom }},</p>
<p>Votre code d'accès SecureAccess : <strong>{{ $code }}</strong></p>
<p>Ce code expire dans {{ config('cyberguard.auth.otp.ttl_minutes', 3) }} minutes.</p>
@endsection
