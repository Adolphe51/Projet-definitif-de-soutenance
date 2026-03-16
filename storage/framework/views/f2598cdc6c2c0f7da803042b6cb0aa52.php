<?php $__env->startSection('title', 'Attaques — CyberGuard'); ?>
<?php $__env->startSection('page-title', '💀 Toutes les Attaques'); ?>

<?php $__env->startPush('styles'); ?>
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
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<!-- Summary bar -->
<div style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap;">
    <?php
        $totals = ['critical'=>0,'high'=>0,'medium'=>0,'low'=>0];
        foreach($attacks as $a) $totals[$a->severity] = ($totals[$a->severity] ?? 0) + 1;
    ?>
    <?php $__currentLoopData = ['critical'=>'💀','high'=>'🔴','medium'=>'🟡','low'=>'🟢']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sev => $icon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div style="
        background:var(--bg-card); border:1px solid var(--border); border-radius:8px;
        padding:10px 16px; display:flex; align-items:center; gap:8px;
        border-left: 3px solid var(--<?php echo e($sev); ?>);
    ">
        <span style="font-size:18px;"><?php echo e($icon); ?></span>
        <div>
            <div style="font-family:'Rajdhani',sans-serif; font-size:22px; font-weight:700; color:var(--<?php echo e($sev); ?>);">
                <?php echo e(\App\Models\Attack::where('severity',$sev)->count()); ?>

            </div>
            <div style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px;"><?php echo e($sev); ?></div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <div style="
        background:var(--bg-card); border:1px solid var(--border); border-radius:8px;
        padding:10px 16px; display:flex; align-items:center; gap:8px; margin-left:auto;
    ">
        <span style="font-size:18px;">📊</span>
        <div>
            <div style="font-family:'Rajdhani',sans-serif; font-size:22px; font-weight:700; color:var(--accent-cyan);">
                <?php echo e($attacks->total()); ?>

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
        <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($t); ?>"><?php echo e($t); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                <?php $__currentLoopData = $attacks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attack): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr data-severity="<?php echo e($attack->severity); ?>" data-type="<?php echo e($attack->type); ?>" data-status="<?php echo e($attack->status); ?>" data-ip="<?php echo e($attack->source_ip); ?>">
                    <td style="color:var(--text-muted); font-family:'Share Tech Mono',monospace; font-size:11px;"><?php echo e($attack->id); ?></td>
                    <td>
                        <div class="attack-type-badge">
                            <?php echo e($attack->type_icon); ?> <?php echo e($attack->type); ?>

                        </div>
                        <?php if($attack->is_simulation): ?>
                        <span class="badge" style="background:rgba(168,85,247,0.15);color:#a855f7;border-color:#a855f7;margin-left:4px;font-size:10px;">SIM</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="ip-addr"><?php echo e($attack->source_ip); ?></span></td>
                    <td style="font-size:12px;">
                        <div>🌍 <?php echo e($attack->city); ?>, <?php echo e($attack->country); ?></div>
                        <?php if($attack->isp): ?>
                        <div style="color:var(--text-muted); font-size:10px; margin-top:2px;"><?php echo e($attack->isp); ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="ip-addr"><?php echo e($attack->target_ip); ?></span>
                        <?php if($attack->target_port): ?>
                        <span style="color:var(--text-muted); font-size:11px;">:<?php echo e($attack->target_port); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge badge-<?php echo e($attack->severity); ?>"><?php echo e($attack->severity_icon); ?> <?php echo e($attack->severity); ?></span></td>
                    <td><span class="status-badge status-<?php echo e($attack->status); ?>"><?php echo e(strtoupper($attack->status)); ?></span></td>
                    <td class="mono" style="font-size:12px;"><?php echo e(number_format($attack->packet_count)); ?></td>
                    <td class="mono" style="font-size:12px;"><?php echo e($attack->bandwidth_mbps); ?> Mbps</td>
                    <td style="font-size:11px; color:var(--text-muted);"><?php echo e($attack->created_at->diffForHumans()); ?></td>
                    <td>
                        <div class="action-btns">
                            <a href="<?php echo e(route('attacks.show', $attack->id)); ?>" class="btn btn-primary btn-sm" title="Détails">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if($attack->status !== 'blocked'): ?>
                            <button class="btn btn-danger btn-sm" onclick="blockAttack(<?php echo e($attack->id); ?>, this)" title="Bloquer">
                                <i class="fas fa-ban"></i>
                            </button>
                            <?php else: ?>
                            <button class="btn btn-sm" style="background:rgba(0,255,136,0.1);color:var(--accent-green);border:1px solid rgba(0,255,136,0.3);" disabled title="Bloqué">
                                <i class="fas fa-check"></i>
                            </button>
                            <?php endif; ?>
                            <button class="btn btn-sm" style="background:rgba(255,0,64,0.1);color:var(--accent-red);border:1px solid rgba(255,0,64,0.3);"
                                onclick="deleteAttack(<?php echo e($attack->id); ?>, this)" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-wrap">
        <?php echo e($attacks->links('pagination::simple-default')); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hp\Desktop\cyberguard\cyberguard\resources\views/attacks/index.blade.php ENDPATH**/ ?>