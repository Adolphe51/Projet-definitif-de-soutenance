@extends('layouts.app')
@section('title', 'Attaques')
@section('page-title', 'Gestion des attaques')
@section('page-subtitle', 'Espace d’investigation des incidents avec priorisation, filtres métier et accès rapide au traitement SOC.')

@section('content')
    @php
        $criticalCount = $severityCounts['critical'] ?? 0;
        $highCount = $severityCounts['high'] ?? 0;
        $mediumCount = $severityCounts['medium'] ?? 0;
        $lowCount = $severityCounts['low'] ?? 0;
        $admin = auth()->user()?->hasRole('admin');
    @endphp

    <section class="dashboard-hero incident-list-hero">
        <div class="dashboard-hero-copy">
            <span class="dashboard-chip">File d’incidents</span>
            <h2>Une vue claire pour trier, traiter et documenter les attaques.</h2>
            <p>
                Cette page sépare l’analyse des incidents du dashboard principal afin de garder un parcours
                plus lisible pendant la démonstration et plus productif pendant le traitement.
            </p>
            <div class="dashboard-actions">
                <a href="{{ route('attacks.live') }}" class="btn btn-primary">Vue temps réel</a>
                <a href="{{ route('geo.attackers') }}" class="btn btn-secondary-outline">Analyse géographique</a>
            </div>
        </div>

        <div class="dashboard-health {{ $criticalCount > 0 ? 'dashboard-health--critical' : 'dashboard-health--low' }}">
            <div class="dashboard-health-label">Priorité actuelle</div>
            <div class="dashboard-health-value">{{ $criticalCount > 0 ? 'Incidents critiques à traiter' : 'Charge maîtrisée' }}</div>
            <div class="dashboard-health-meta">
                {{ $criticalCount }} critique(s) · {{ $attacks->total() }} incident(s) observé(s) · {{ count($types) }} type(s) connus
            </div>
            <div class="dashboard-health-stats">
                <div>
                    <strong>{{ $highCount + $criticalCount }}</strong>
                    <span>niveau élevé</span>
                </div>
                <div>
                    <strong>{{ $mediumCount + $lowCount }}</strong>
                    <span>niveau modéré</span>
                </div>
            </div>
        </div>
    </section>

    <section class="attacks-overview-grid">
        <article class="attacks-overview-card attacks-overview-card--critical">
            <span class="attacks-overview-label">Critiques</span>
            <strong>{{ $criticalCount }}</strong>
            <p>Incidents à isoler ou bloquer en priorité.</p>
        </article>
        <article class="attacks-overview-card attacks-overview-card--high">
            <span class="attacks-overview-label">Élevées</span>
            <strong>{{ $highCount }}</strong>
            <p>Surveillance renforcée et validation rapide du risque.</p>
        </article>
        <article class="attacks-overview-card attacks-overview-card--medium">
            <span class="attacks-overview-label">Moyennes</span>
            <strong>{{ $mediumCount }}</strong>
            <p>Incidents à qualifier avant action supplémentaire.</p>
        </article>
        <article class="attacks-overview-card attacks-overview-card--neutral">
            <span class="attacks-overview-label">Total</span>
            <strong>{{ $attacks->total() }}</strong>
            <p>Vision globale de l’activité captée par CyberGuard.</p>
        </article>
    </section>

    <section class="card dashboard-panel attacks-filter-card">
        <div class="section-header">
            <div>
                <div class="section-title">Filtres d’analyse</div>
                <p class="section-intro">Affiche rapidement la bonne tranche d’incidents sans surcharger le dashboard.</p>
            </div>
        </div>

        <div class="attacks-filter-grid">
            <label class="filter-field">
                <span class="form-label">Sévérité</span>
                <select id="filter-severity" class="form-control" onchange="applyFilters()">
                    <option value="">Toutes les sévérités</option>
                    <option value="critical">Critique</option>
                    <option value="high">Élevée</option>
                    <option value="medium">Moyenne</option>
                    <option value="low">Faible</option>
                </select>
            </label>

            <label class="filter-field">
                <span class="form-label">Type d’attaque</span>
                <select id="filter-type" class="form-control" onchange="applyFilters()">
                    <option value="">Tous les types</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </label>

            <label class="filter-field">
                <span class="form-label">Statut</span>
                <select id="filter-status" class="form-control" onchange="applyFilters()">
                    <option value="">Tous les statuts</option>
                    <option value="detected">Détectée</option>
                    <option value="blocked">Bloquée</option>
                    <option value="investigating">En cours</option>
                    <option value="resolved">Résolue</option>
                    <option value="false_positive">Faux positif</option>
                </select>
            </label>

            <label class="filter-field">
                <span class="form-label">Source IP</span>
                <input
                    type="text"
                    id="filter-ip"
                    class="form-control"
                    placeholder="Rechercher une IP source"
                    oninput="applyFilters()"
                >
            </label>
        </div>

        <div class="attacks-filter-toolbar">
            <div class="attacks-filter-summary" id="visible-attack-count">
                {{ $attacks->count() }} incident(s) affiché(s) sur cette page
            </div>

            <div class="attacks-filter-actions">
                @if($admin)
                    <button class="btn btn-danger btn-sm" onclick="blockAllCritical()">
                        Bloquer les critiques visibles
                    </button>
                @endif
                <button class="btn btn-secondary-outline btn-sm" onclick="exportCSV()">
                    Export CSV
                </button>
            </div>
        </div>
    </section>

    <section class="card dashboard-panel">
        <div class="section-header">
            <div>
                <div class="section-title">Liste des incidents</div>
                <p class="section-intro">Chaque ligne donne l’essentiel pour décider d’investiguer, bloquer ou consulter le détail.</p>
            </div>
        </div>

        @if($attacks->count() > 0)
            <div class="table-wrap">
                <table class="data-table attack-list-table" id="attacks-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Attaque</th>
                            <th>Source</th>
                            <th>Cible</th>
                            <th>Sévérité</th>
                            <th>Statut</th>
                            <th>Volume</th>
                            <th>Détection</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="attacks-tbody">
                        @foreach($attacks as $attack)
                            <tr
                                data-severity="{{ $attack->severity }}"
                                data-type="{{ $attack->type }}"
                                data-status="{{ $attack->status }}"
                                data-ip="{{ $attack->source_ip }}"
                            >
                                <td class="mono text-muted-small">#{{ $attack->id }}</td>
                                <td>
                                    <div class="attack-table-title">
                                        <span class="attack-type-badge">{{ $attack->type_icon }} {{ $attack->type }}</span>
                                        @if($attack->is_simulation)
                                            <span class="badge badge-simulation">Simulation</span>
                                        @endif
                                    </div>
                                    <div class="attack-table-subtext">
                                        {{ $attack->city ?: 'Ville inconnue' }}, {{ $attack->country ?: 'Pays inconnu' }}
                                        @if($attack->isp)
                                            · {{ $attack->isp }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="attack-table-title mono">{{ $attack->source_ip }}</div>
                                    <div class="attack-table-subtext">Origine observée</div>
                                </td>
                                <td>
                                    <div class="attack-table-title mono">
                                        {{ $attack->target_ip }}
                                        @if($attack->target_port)
                                            :{{ $attack->target_port }}
                                        @endif
                                    </div>
                                    <div class="attack-table-subtext">Ressource ciblée</div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $attack->severity }}">{{ $attack->severity_icon }} {{ strtoupper($attack->severity) }}</span>
                                </td>
                                <td>
                                    <span class="status-badge status-{{ $attack->status }}">{{ strtoupper(str_replace('_', ' ', $attack->status)) }}</span>
                                </td>
                                <td>
                                    <div class="attack-table-title mono">{{ number_format($attack->packet_count) }} paquets</div>
                                    <div class="attack-table-subtext mono">{{ number_format($attack->bandwidth_mbps, 1) }} Mbps</div>
                                </td>
                                <td>
                                    <div class="attack-table-title">{{ $attack->created_at->diffForHumans() }}</div>
                                    <div class="attack-table-subtext">{{ $attack->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>
                                    <div class="attack-table-actions">
                                        <a href="{{ route('attacks.show', $attack->id) }}" class="btn btn-primary btn-sm">
                                            Détails
                                        </a>

                                        @if($admin)
                                            @if($attack->status !== 'blocked')
                                                <button class="btn btn-danger btn-sm" onclick="blockAttack({{ $attack->id }}, this)">
                                                    Bloquer
                                                </button>
                                            @else
                                                <button class="btn btn-success btn-sm" disabled>
                                                    Bloquée
                                                </button>
                                            @endif

                                            <button class="btn btn-secondary-outline btn-sm" onclick="deleteAttack({{ $attack->id }}, this)">
                                                Supprimer
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrap">
                {{ $attacks->links() }}
            </div>
        @else
            <div class="empty-state">
                <p class="empty-state-title">Aucune attaque enregistrée</p>
                <p class="empty-state-text">Les incidents détectés apparaîtront ici avec leurs statuts et leurs actions de traitement.</p>
            </div>
        @endif
    </section>
@endsection

@push('scripts')
<script>
function updateVisibleCount() {
    const rows = Array.from(document.querySelectorAll('#attacks-tbody tr'));
    const visibleRows = rows.filter(row => row.style.display !== 'none');
    const summary = document.getElementById('visible-attack-count');

    if (summary) {
        summary.textContent = `${visibleRows.length} incident(s) affiché(s) sur cette page`;
    }
}

function applyFilters() {
    const severity = document.getElementById('filter-severity').value.toLowerCase();
    const type = document.getElementById('filter-type').value.toLowerCase();
    const status = document.getElementById('filter-status').value.toLowerCase();
    const ip = document.getElementById('filter-ip').value.toLowerCase();

    document.querySelectorAll('#attacks-tbody tr').forEach(row => {
        const matches =
            (!severity || row.dataset.severity === severity) &&
            (!type || row.dataset.type.toLowerCase().includes(type)) &&
            (!status || row.dataset.status === status) &&
            (!ip || row.dataset.ip.toLowerCase().includes(ip));

        row.style.display = matches ? '' : 'none';
    });

    updateVisibleCount();
}

async function blockAttack(id, btn) {
    btn.disabled = true;
    btn.textContent = '...';

    try {
        const response = await csrfFetch(`/attacks/block/${id}`, { method: 'POST' });
        const data = await response.json();

        if (data.success) {
            showToast(data.message, data.already ? 'info' : 'success');

            const row = btn.closest('tr');
            const badge = row.querySelector('.status-badge');
            badge.textContent = 'BLOCKED';
            badge.className = 'status-badge status-blocked';

            btn.outerHTML = '<button class="btn btn-success btn-sm" disabled>Bloquée</button>';
        }
    } catch (error) {
        btn.disabled = false;
        btn.textContent = 'Bloquer';
        showToast('Le blocage n’a pas pu être appliqué.', 'error');
    }
}

async function deleteAttack(id, btn) {
    if (!confirm('Supprimer cette attaque ?')) {
        return;
    }

    try {
        await csrfFetch(`/attacks/${id}`, { method: 'DELETE' });
        btn.closest('tr').remove();
        updateVisibleCount();
        showToast('L’attaque a été retirée de la liste.', 'success', 3000);
    } catch (error) {
        showToast('La suppression a échoué.', 'error');
    }
}

async function blockAllCritical() {
    const rows = Array.from(document.querySelectorAll('#attacks-tbody tr[data-severity="critical"]'))
        .filter(row => row.style.display !== 'none');

    if (!rows.length) {
        showToast('Aucune attaque critique visible à bloquer.', 'info');
        return;
    }

    if (!confirm('Bloquer toutes les attaques critiques visibles ?')) {
        return;
    }

    showToast(`${rows.length} attaque(s) critique(s) en cours de blocage.`, 'warning');

    for (const row of rows) {
        const button = row.querySelector('button[onclick^="blockAttack"]');
        const id = button?.getAttribute('onclick')?.match(/\d+/)?.[0];

        if (id) {
            await csrfFetch(`/attacks/block/${id}`, { method: 'POST' });
        }
    }

    showToast('Les attaques critiques visibles ont été bloquées.', 'success');
    window.setTimeout(() => window.location.reload(), 1200);
}

function exportCSV() {
    const csvRows = [['ID', 'Type', 'Source IP', 'Localisation', 'Cible', 'Sévérité', 'Statut', 'Paquets', 'Bande passante', 'Détection']];

    document.querySelectorAll('#attacks-tbody tr').forEach(row => {
        if (row.style.display === 'none') {
            return;
        }

        const cells = row.querySelectorAll('td');
        csvRows.push([
            cells[0]?.textContent?.trim() ?? '',
            cells[1]?.querySelector('.attack-type-badge')?.textContent?.trim() ?? '',
            cells[2]?.querySelector('.attack-table-title')?.textContent?.trim() ?? '',
            cells[1]?.querySelector('.attack-table-subtext')?.textContent?.trim() ?? '',
            cells[3]?.querySelector('.attack-table-title')?.textContent?.trim() ?? '',
            cells[4]?.textContent?.trim() ?? '',
            cells[5]?.textContent?.trim() ?? '',
            cells[6]?.querySelector('.attack-table-title')?.textContent?.trim() ?? '',
            cells[6]?.querySelector('.attack-table-subtext')?.textContent?.trim() ?? '',
            cells[7]?.querySelector('.attack-table-title')?.textContent?.trim() ?? '',
        ]);
    });

    const csv = csvRows.map(row => row.map(col => `"${String(col).replace(/"/g, '""')}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');

    link.href = url;
    link.download = 'cyberguard_attacks.csv';
    link.click();

    URL.revokeObjectURL(url);
}

updateVisibleCount();
</script>
@endpush
