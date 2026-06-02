<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'CyberGuard') — CyberGuard</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;700&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/dash.js'])
    @if(request()->routeIs('intranet.*'))
        @vite(['resources/css/intranet.css', 'resources/css/components.css', 'resources/js/intranet.js'])
    @endif
    @stack('styles')
</head>

<body class="{{ request()->routeIs('intranet.*') ? 'intranet' : '' }}">
    @php
$isAdmin = auth()->user()?->hasRole('admin');
$backFallbackUrl = match (true) {
    request()->routeIs('attacks.show') => route('attacks.index'),
    request()->routeIs('attacks.live') => route('attacks.index'),
    request()->routeIs('geo.trace') => route('geo.attackers'),
    request()->routeIs('geo.attackers') => route('attacks.index'),
    request()->routeIs('alerts.*') => route('dashboard'),
    request()->routeIs('honeypot.detail') => route('honeypot.index'),
    request()->routeIs('honeypot.*') => route('dashboard'),
    request()->routeIs('simulations.*') => route('dashboard'),
    request()->routeIs('intranet.students.*') => route('intranet.students.index'),
    request()->routeIs('intranet.courses.*') => route('intranet.courses.index'),
    request()->routeIs('intranet.messages.*') => route('intranet.messages.index'),
    request()->routeIs('intranet.*') => route('intranet.index'),
    default => $isAdmin ? route('dashboard') : route('intranet.index'),
};
$showBackButton = auth()->check() && (!request()->routeIs('dashboard') || request()->routeIs('intranet.*'));
    @endphp

    @if(request()->routeIs('intranet.*'))
            @php
        $pageTitle = trim($__env->yieldContent('page-title')) ?: 'Application métier de démonstration';
        $pageSubtitle = trim($__env->yieldContent('page-subtitle')) ?: 'Parcours applicatif sécurisé pour démontrer authentification, audit des actions et remontée d’événements vers CyberGuard.';
        $intranetNavGroups = [
            [
                'label' => 'Parcours métier',
                'items' => [
                    ['label' => 'Accueil', 'route' => 'intranet.index', 'active' => ['intranet.index']],
                    ['label' => 'Usagers', 'route' => 'intranet.students.index', 'active' => ['intranet.students.*']],
                    ['label' => 'Services', 'route' => 'intranet.courses.index', 'active' => ['intranet.courses.*']],
                    ['label' => 'Messages', 'route' => 'intranet.messages.index', 'active' => ['intranet.messages.*']],
                ],
            ],
            [
                'label' => 'CyberGuard',
                'items' => $isAdmin ? [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => ['dashboard']],
                    ['label' => 'Alertes', 'route' => 'alerts.index', 'active' => ['alerts.*'], 'count' => $globalUnreadAlerts ?? 0],
                    ['label' => 'Incidents', 'route' => 'attacks.index', 'active' => ['attacks.index', 'attacks.show', 'attacks.live']],
                ] : [],
            ],
        ];
            @endphp

            <div class="app-shell intranet-shell">
                <aside class="app-sidebar intranet-sidebar" aria-label="Site métier">
                    <a href="{{ route('intranet.index') }}" class="app-brand">
                        <span>
                            <svg width="60" height="60" viewBox="0 0 60 60">
                                <circle cx="30" cy="30" r="28" fill="#16a34a;" />
                                <path d="M20 30L30 20L40 30L30 40Z" fill="white" />
                            </svg>
                        </span>
                        <span>
                            <strong>Application métier</strong>
                            <small>Surface applicative de démonstration</small>
                        </span>
                    </a>

                    <div class="app-sidebar-status">
                        <span class="app-status-dot"></span>
                        Parcours journalisé
                    </div>

                    <nav class="app-nav" aria-label="Navigation mini site">
                        @foreach($intranetNavGroups as $group)
                            @if(!empty($group['items']))
                                <div class="app-nav-group">
                                    <div class="app-nav-label">{{ $group['label'] }}</div>
                                    @foreach($group['items'] as $item)
                                        @php
                    $isActive = collect($item['active'] ?? [])->contains(fn($pattern) => request()->routeIs($pattern));
                                        @endphp
                                        <a href="{{ route($item['route']) }}" class="app-nav-link {{ $isActive ? 'is-active' : '' }}">
                                            <span>{{ $item['label'] }}</span>
                                            @if(array_key_exists('count', $item))
                                                <span class="app-nav-count" data-alert-count>{{ $item['count'] }}</span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    </nav>
                </aside>

                <div class="app-main">
                    <header class="app-topbar intranet-topbar">
                        <div>
                            <div class="app-kicker">Module applicatif de démonstration sécurisé</div>
                            <h1 class="app-page-title">{{ $pageTitle }}</h1>
                            <p class="app-page-subtitle">{{ $pageSubtitle }}</p>
                        </div>

                        <div class="app-topbar-actions">
                            @if($showBackButton)
                                <button type="button" class="app-alert-pill app-history-pill"
                                    onclick="navigateToPrevious(@js($backFallbackUrl))">
                                    Retour
                                </button>
                            @endif
                            @if($isAdmin)
                                <a href="{{ route('dashboard') }}" class="app-alert-pill">
                                    Retour SOC
                                </a>
                                <a href="{{ route('alerts.index') }}" class="app-alert-pill">
                                    Alertes
                                    <span data-alert-count>{{ $globalUnreadAlerts ?? 0 }}</span>
                                </a>
                            @endif
                            @include('layouts.header')
                        </div>
                    </header>

                    <main class="app-content intranet-content">
                        @yield('content')
                    </main>
                </div>

                <div id="toastContainer" class="toast-container"></div>
            </div>
    @else
        @php
    $pageTitle = trim($__env->yieldContent('page-title')) ?: 'Centre de supervision';
    $pageSubtitle = trim($__env->yieldContent('page-subtitle')) ?: 'Navigation claire entre supervision, incidents, audit de sécurité et simulations manuelles.';
    $navGroups = [
        [
            'label' => 'Supervision',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => ['dashboard']],
                ['label' => 'Alertes', 'route' => 'alerts.index', 'active' => ['alerts.*'], 'count' => $globalUnreadAlerts ?? 0],
            ],
        ],
        [
            'label' => 'Incidents',
            'items' => [
                ['label' => 'Attaques', 'route' => 'attacks.index', 'active' => ['attacks.index', 'attacks.show']],
                ['label' => 'Live', 'route' => 'attacks.live', 'active' => ['attacks.live']],
                ['label' => 'Géolocalisation', 'route' => 'geo.attackers', 'active' => ['geo.attackers', 'geo.trace']],
            ],
        ],
        [
            'label' => 'Laboratoire',
            'items' => array_values(array_filter([
                $isAdmin ? ['label' => 'Simulations', 'route' => 'simulations.index', 'active' => ['simulations.*']] : null,
            ])),
        ],
    ];
        @endphp

        <div class="app-shell">
            <aside class="app-sidebar">
                <a href="{{ route('dashboard') }}" class="app-brand">
                    <span>
                        <svg width="60" height="60" viewBox="0 0 60 60">
                            <circle cx="30" cy="30" r="28" fill="#16a34a;" />
                            <path d="M20 30L30 20L40 30L30 40Z" fill="white" />
                        </svg>
                    </span>
                    <span>
                        <strong>CyberGuard</strong>
                        <small>Supervision sécurité</small>
                    </span>
                </a>

                <div class="app-sidebar-status">
                    <span class="app-status-dot"></span>
                    Surveillance active
                </div>

                <nav class="app-nav" aria-label="Navigation CyberGuard">
                    @foreach($navGroups as $group)
                        @if(!empty($group['items']))
                            <div class="app-nav-group">
                                <div class="app-nav-label">{{ $group['label'] }}</div>
                                @foreach($group['items'] as $item)
                                    @php
                $isActive = collect($item['active'] ?? [])->contains(fn($pattern) => request()->routeIs($pattern));
                                    @endphp
                                    <a href="{{ route($item['route']) }}" class="app-nav-link {{ $isActive ? 'is-active' : '' }}">
                                        <span>{{ $item['label'] }}</span>
                                        @if(array_key_exists('count', $item))
                                            <span class="app-nav-count" id="nav-alert-count" data-alert-count>{{ $item['count'] }}</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </nav>
            </aside>

            <div class="app-main">
                <header class="app-topbar">
                    <div>
                        <div class="app-kicker">Module CyberGuard</div>
                        <h1 class="app-page-title">{{ $pageTitle }}</h1>
                        <p class="app-page-subtitle">{{ $pageSubtitle }}</p>
                    </div>

                    <div class="app-topbar-actions">
                        @if($showBackButton)
                            <button type="button" class="app-alert-pill app-history-pill"
                                onclick="navigateToPrevious(@js($backFallbackUrl))">
                                Retour
                            </button>
                        @endif
                        <a href="{{ route('alerts.index') }}" class="app-alert-pill">
                            Alertes
                            <span id="topbar-alert-count" data-alert-count>{{ $globalUnreadAlerts ?? 0 }}</span>
                        </a>
                        @include('layouts.header')
                    </div>
                </header>

                <main class="app-content">
                    @yield('content')
                </main>
            </div>
        </div>

        <div id="toastContainer" class="toast-container"></div>
    @endif

    @if(session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                console.log('Session success détectée:', "{{ session('success') }}");
                toast.success("{{ session('success') }}");
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                console.log('Session error détectée:', "{{ session('error') }}");
                toast.error("{{ session('error') }}");
            });
        </script>
    @endif

    @if(session('info'))
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                console.log('Session info détectée:', "{{ session('info') }}");
                toast.info("{{ session('info') }}");
            });
        </script>
    @endif

    @if(session('warning'))
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                console.log('Session warning détectée:', "{{ session('warning') }}");
                toast.warning("{{ session('warning') }}");
            });
        </script>
    @endif

    <script>
        function navigateToPrevious(fallbackUrl) {
            const referrer = document.referrer ? new URL(document.referrer, window.location.origin) : null;
            const current = new URL(window.location.href);
            const isSameOrigin = referrer && referrer.origin === current.origin;
            const isDifferentPage = referrer && referrer.href !== current.href;
            const isAuthScreen = referrer && ['/login', '/otp/verify'].includes(referrer.pathname);

            if (isSameOrigin && isDifferentPage && !isAuthScreen && window.history.length > 1) {
                window.history.back();
                return;
            }

            window.location.href = fallbackUrl;
        }
    </script>

    @stack('scripts')
</body>

</html>
