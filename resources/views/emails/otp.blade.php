@extends('layouts.auth.app')

@section('title', 'OTP')

@section('content')
<p>Bonjour {{ $authCode->user->name }},</p>
<p>Votre code d'accès SecureAccess : <strong>{{ $code }}</strong></p>
<p>Ce code expire dans 10 minutes.</p>
@endsection