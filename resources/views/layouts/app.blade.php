<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'CyberGuard') — CyberGuard</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/dash.js'])
    @if(request()->routeIs('intranet.*'))
        @vite(['resources/css/intranet.css', 'resources/css/components.css', 'resources/js/intranet.js'])
    @endif
</head>

<body class="{{ request()->routeIs('intranet.*') ? 'intranet' : '' }}">

    <div class="auth-container">

        <main class="auth-card">

            @include('layouts.header')

            @if(request()->routeIs('intranet.*'))
                <nav class="intranet-nav">
                    <div class="intranet-nav-inner">
                        <a href="{{ route('intranet.index') }}">Accueil</a>
                        <a href="{{ route('intranet.students.index') }}">Étudiants</a>
                        <a href="{{ route('intranet.courses.index') }}">Cours</a>
                        <a href="{{ route('intranet.messages.index') }}">Messages</a>
                        <a href="{{ route('intranet.enrollments.index') }}">Inscriptions</a>
                        <a href="{{ route('intranet.attendances.index') }}">Présences</a>
                        <a href="{{ route('intranet.components') }}" class="small-link">📚 Composants</a>
                    </div>
                </nav>
            @endif

            <section class="auth-body">
                @yield('content')
            </section>

        </main>

        @include('layouts.footer')

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

</body>

</html>