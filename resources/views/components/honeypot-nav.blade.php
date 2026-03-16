        <!-- ajout dans la nav après "Outils" section -->
        <a href="{{ route('honeypot.index') }}" class="nav-item {{ request()->routeIs('honeypot.*') ? 'active' : '' }}">
            <span class="nav-icon">🍯</span>
            <span>Honeypot</span>
            @php $hpTriggered = \App\Models\HoneypotTrap::where('status','triggered')->count(); @endphp
            @if($hpTriggered > 0)
            <span class="nav-badge" style="background:var(--accent-yellow);color:#000;">{{ $hpTriggered }}</span>
            @endif
        </a>
