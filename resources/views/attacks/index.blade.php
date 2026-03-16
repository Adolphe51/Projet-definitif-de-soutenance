@extends('layouts.app')
@section('title', 'Attaques — CyberGuard')
@section('page-title', '💀 Toutes les Attaques')

@push('styles')
<style>
.filter-panel {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 16px 20px;
    margin-bottom: 20px;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
}
.filter-panel select, .filter-panel input {
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 6px;
    color: var(--text-primary);
    padding: 8px 12px;
    font-family: 'Share Tech Mono', monospace;
    font-size: 12px;
    outline: none;
    transition: border-color 0.2s;
}
.filter-panel select:focus, .filter-panel input:focus {
    border-color: var(--accent-cyan);
}
.table-wrap { overflow-x: auto; }
.action-btns { display: flex; gap: 4px; }
.attack-type-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 8px;
    background: rgba(0,229,255,0.08);
    border: 1px solid rgba(0,229,255,0.2);
    border-radius: 5px;
    font-size: 12px;
    font-weight: 600;
    color: var(--accent-cyan);
}
.status-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 8px; border-radius: 4px;
    font-family: 'Share Tech Mono', monospace; font-size: 11px;
}
.status-detected    { background: rgba(255,107,0,0.12); color: var(--high);       border: 1px solid rgba(255,107,0,0.3); }
.status-blocked     { background: rgba(0,255,136,0.1);  color: var(--accent-green);border: 1px solid rgba(0,255,136,0.3); }
.status-investigating { background: rgba(0,229,255,0.1); color: var(--accent-cyan); border: 1px solid rgba(0,229,255,0.3); }
.pagination-wrap { display: flex; justify-content: center; margin-top: 20px; gap: 6px; }
.page-btn {
    padding: 6px 12px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 6px;
    color: var(--text-muted);
    cursor: pointer;
    font-size: 13px;
    text-decoration: none;
    transition: all 0.2s;
}
.page-btn:hover, .page-btn.active {
    border-color: var(--accent-cyan);
    color: var(--accent-cyan);
    background: rgba(0,229,255,0.08);
}
</style>
@endpush

@section('content')

<!-- Summary bar -->
<div style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap;">
    @php
        $totals = ['critical'=>0,'high'=>0,'medium'=>0,'low'=>0];
        foreach($attacks as $a) $totals[$a->severity] = ($totals[$a->severity] ?? 0) + 1;
    @endphp
    @foreach(['critical'=>'💀','high'=>'🔴','medium'=>'🟡','low'=>'🟢'] as $sev => $icon)
    <div style="
        background:var(--bg-card); border:1px solid var(--border); border-radius:8px;
        padding:10px 16px; display:flex; align-items:center; gap:8px;
        border-left: 3px solid var(--{{ $sev }});
    ">
        <span style="font-size:18px;">{{ $icon }}</span>
        <div>
            <div style="font-family:'Rajdhani',sans-serif; font-size:22px; font-weight:700; color:var(--{{ $sev }});">
                {{ \App\Models\Attack::where('severity',$sev)->count() }}
            </div>
            <div style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px;">{{ $sev }}</div>
        </div>
    </div>
    @endforeach
    <div style="
        background:var(--bg-card); border:1px solid var(--border); border-radius:8px;
        padding:10px 16px; display:flex; align-items:center; gap:8px; margin-left:auto;
    ">
        <span style="font-size:18px;">📊</span>
        <div>
            <div style="font-family:'Rajdhani',sans-serif; font-size:22px; font-weight:700; color:var(--accent-cyan);">
                {{ $attacks->total() }}
            </div>
            <div style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px;">Total</div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="filter-panel">
    <i class="fas fa-filter" style="color:var(--text-muted);"></i>
    <select id="filter-severity" onchange="applyFilters()">
        <option value="">Toutes sévérités</option>
        <option value="critical">Critique</option>
        <option value="high">Élevée</option>
        <option value="medium">Moyenne</option>
        <option value="low">Faible</option>
    </select>
    <select id="filter-type" onchange="applyFilters()">
        <option value="">Tous types</option>
        @foreach($types as $t)
        <option value="{{ $t }}">{{ $t }}</option>
        @endforeach
    </select>
    <select id="filter-status" onchange="applyFilters()">
        <option value="">Tous statuts</option>
        <option value="detected">Détectée</option>
        <option value="blocked">Bloquée</option>
        <option value="investigating">En cours</option>
    </select>
    <input type="text" id="filter-ip" placeholder="Filtrer par IP..." oninput="applyFilters()">
    <button class="btn btn-danger btn-sm" style="margin-left:auto;" onclick="blockAllCritical()">
        <i class="fas fa-ban"></i> Bloquer tous Critiques
    </button>
    <button class="btn btn-primary btn-sm" onclick="exportCSV()">
        <i class="fas fa-download"></i> Export CSV
    </button>
</div>

<!-- Table -->
<div class="card">
    <div class="table-wrap">
        <table class="data-table" id="attacks-table">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th>Type</th>
                    <th>Source IP</th>
                    <th>Localisation</th>
                    <th>Cible</th>
                    <th>Sévérité</th>
                    <th>Statut</th>
                    <th>Paquets</th>
                    <th>Bande passante</th>
                    <th>Détecté</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="attacks-tbody">
                @foreach($attacks as $attack)
                <tr data-severity="{{ $attack->severity }}" data-type="{{ $attack->type }}" data-status="{{ $attack->status }}" data-ip="{{ $attack->source_ip }}">
                    <td style="color:var(--text-muted); font-family:'Share Tech Mono',monospace; font-size:11px;">{{ $attack->id }}</td>
                    <td>
                        <div class="attack-type-badge">
                            {{ $attack->type_icon }} {{ $attack->type }}
                        </div>
                        @if($attack->is_simulation)
                        <span class="badge" style="background:rgba(168,85,247,0.15);color:#a855f7;border-color:#a855f7;margin-left:4px;font-size:10px;">SIM</span>
                        @endif
                    </td>
                    <td><span class="ip-addr">{{ $attack->source_ip }}</span></td>
                    <td style="font-size:12px;">
                        <div>🌍 {{ $attack->city }}, {{ $attack->country }}</div>
                        @if($attack->isp)
                        <div style="color:var(--text-muted); font-size:10px; margin-top:2px;">{{ $attack->isp }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="ip-addr">{{ $attack->target_ip }}</span>
                        @if($attack->target_port)
                        <span style="color:var(--text-muted); font-size:11px;">:{{ $attack->target_port }}</span>
                        @endif
                    </td>
                    <td><span class="badge badge-{{ $attack->severity }}">{{ $attack->severity_icon }} {{ $attack->severity }}</span></td>
                    <td><span class="status-badge status-{{ $attack->status }}">{{ strtoupper($attack->status) }}</span></td>
                    <td class="mono" style="font-size:12px;">{{ number_format($attack->packet_count) }}</td>
                    <td class="mono" style="font-size:12px;">{{ $attack->bandwidth_mbps }} Mbps</td>
                    <td style="font-size:11px; color:var(--text-muted);">{{ $attack->created_at->diffForHumans() }}</td>
                    <td>
                        <div class="action-btns">
                            <a href="{{ route('attacks.show', $attack->id) }}" class="btn btn-primary btn-sm" title="Détails">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($attack->status !== 'blocked')
                            <button class="btn btn-danger btn-sm" onclick="blockAttack({{ $attack->id }}, this)" title="Bloquer">
                                <i class="fas fa-ban"></i>
                            </button>
                            @else
                            <button class="btn btn-sm" style="background:rgba(0,255,136,0.1);color:var(--accent-green);border:1px solid rgba(0,255,136,0.3);" disabled title="Bloqué">
                                <i class="fas fa-check"></i>
                            </button>
                            @endif
                            <button class="btn btn-sm" style="background:rgba(255,0,64,0.1);color:var(--accent-red);border:1px solid rgba(255,0,64,0.3);"
                                onclick="deleteAttack({{ $attack->id }}, this)" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-wrap">
        {{ $attacks->links('pagination::simple-default') }}
    </div>
</div>
@endsection

@push('scripts')
<script>
function applyFilters() {
    const sev    = document.getElementById('filter-severity').value.toLowerCase();
    const type   = document.getElementById('filter-type').value.toLowerCase();
    const status = document.getElementById('filter-status').value.toLowerCase();
    const ip     = document.getElementById('filter-ip').value.toLowerCase();

    document.querySelectorAll('#attacks-tbody tr').forEach(row => {
        const match =
            (!sev    || row.dataset.severity === sev) &&
            (!type   || row.dataset.type.toLowerCase().includes(type)) &&
            (!status || row.dataset.status === status) &&
            (!ip     || row.dataset.ip.includes(ip));
        row.style.display = match ? '' : 'none';
    });
}

async function blockAttack(id, btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    try {
        const res  = await csrfFetch(`/attacks/block/${id}`, { method: 'POST' });
        const data = await res.json();
        if (data.success) {
            showToast('🛡️ IP Bloquée', data.message, 'low');
            btn.closest('tr').querySelector('.status-badge').textContent = 'BLOQUÉ';
            btn.closest('tr').querySelector('.status-badge').className = 'status-badge status-blocked';
            btn.outerHTML = `<button class="btn btn-sm" style="background:rgba(0,255,136,0.1);color:var(--accent-green);border:1px solid rgba(0,255,136,0.3);" disabled><i class="fas fa-check"></i></button>`;
        }
    } catch (e) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-ban"></i>'; }
}

async function deleteAttack(id, btn) {
    if (!confirm('Supprimer cette attaque ?')) return;
    await csrfFetch(`/attacks/${id}`, { method: 'DELETE' });
    btn.closest('tr').style.animation = 'rowAppear 0.3s ease-out reverse';
    setTimeout(() => btn.closest('tr').remove(), 300);
    showToast('🗑️ Supprimé', 'Attaque supprimée.', 'low', 3000);
}

async function blockAllCritical() {
    if (!confirm('Bloquer toutes les IPs critiques ?')) return;
    const rows = document.querySelectorAll('#attacks-tbody tr[data-severity="critical"]');
    showToast('⏳ En cours...', `Blocage de ${rows.length} IPs critiques`, 'medium');
    for (const row of rows) {
        const id = row.querySelector('[onclick^="blockAttack"]')?.getAttribute('onclick')?.match(/\d+/)?.[0];
        if (id) await csrfFetch(`/attacks/block/${id}`, { method: 'POST' });
    }
    showToast('🛡️ Terminé', 'Toutes les IPs critiques bloquées', 'low');
    setTimeout(() => location.reload(), 1500);
}

function exportCSV() {
    const rows = [['ID','Type','Source IP','Pays','Ville','Cible','Sévérité','Statut','Paquets','Détecté']];
    document.querySelectorAll('#attacks-tbody tr').forEach(row => {
        const cells = row.querySelectorAll('td');
        rows.push([
            cells[0]?.textContent?.trim(),
            cells[1]?.textContent?.trim().replace(/\s+/g,' '),
            cells[2]?.textContent?.trim(),
            cells[3]?.textContent?.trim().replace(/\s+/g,' '),
            cells[4]?.textContent?.trim(),
            cells[5]?.textContent?.trim(),
            cells[6]?.textContent?.trim(),
            cells[7]?.textContent?.trim(),
            cells[9]?.textContent?.trim(),
        ]);
    });
    const csv  = rows.map(r => r.map(c => `"${c}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a'); a.href = url; a.download = 'cyberguard_attacks.csv'; a.click();
}
</script>
@endpush
