<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'CyberGuard') — CyberGuard</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/auth.css', 'resources/js/auth.js'])
</head>

<body>

    <div class="auth-container">

        <main class="auth-card">

            @include('layouts.auth.header')

            <section class="auth-body">
                @yield('content')
            </section>

        </main>

        <div id="toastContainer" class="toast-container"></div>

    </div>

    @if(session('success'))
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            console.log('Session success détectée:', "{{session('success')}}");
            toast.success("{{session('success')}}");
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            console.log('Session error détectée:', "{{session('error')}}");
            toast.error("{{session('error')}}");
        });
    </script>
    @endif

    @if(session('info'))
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            console.log('Session info détectée:', "{{session('info')}}");
            toast.info("{{session('info')}}");
        });
    </script>
    @endif

    @if(session('warning'))
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            console.log('Session warning détectée:', "{{session('warning')}}");
            toast.warning("{{session('warning')}}");
        });
    </script>
    @endif

    @if(session('debug_otp_toast'))
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            toast.show("Code OTP (développement) : {{session('debug_otp_toast')}}", "info", 45000);
        })
    </script>
    @endif

</body>

</html>
