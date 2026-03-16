{{-- canary_document.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>RAPPORT_CONFIDENTIEL_Q4_2024.pdf</title>
    <style>
        body { background:#f0f0f0; font-family:Arial,sans-serif; display:flex; align-items:center; justify-content:center; min-height:100vh; }
        .doc { background:#fff; width:700px; min-height:900px; padding:60px; box-shadow:0 4px 20px rgba(0,0,0,.15); position:relative; }
        .watermark {
            position:absolute; top:50%; left:50%; transform:translate(-50%,-50%) rotate(-30deg);
            font-size:80px; color:rgba(255,0,0,0.08); font-weight:bold; pointer-events:none;
            white-space:nowrap; z-index:0;
        }
        .content { position:relative; z-index:1; }
        h1 { color:#cc0000; font-size:18px; text-align:center; border-bottom:2px solid #cc0000; padding-bottom:12px; margin-bottom:20px; }
        h2 { font-size:14px; color:#333; margin:20px 0 8px; }
        p  { font-size:13px; line-height:1.8; color:#444; }
        .classified { background:#cc0000; color:#fff; padding:6px 16px; display:inline-block; font-size:12px; font-weight:bold; letter-spacing:2px; margin:4px 0; }
        table { width:100%; border-collapse:collapse; margin-top:12px; font-size:12px; }
        th { background:#cc0000; color:#fff; padding:8px; text-align:left; }
        td { padding:7px 8px; border-bottom:1px solid #eee; }
        .alert-banner {
            background:#ff0;
            border:2px solid #cc0000;
            padding:12px;
            text-align:center;
            font-weight:bold;
            font-size:13px;
            color:#cc0000;
            margin-bottom:20px;
        }
    </style>
</head>
<body>
    <div class="doc">
        <div class="watermark">CONFIDENTIEL</div>
        <div class="content">
            <div class="alert-banner">⚠️ CANARY TOKEN DÉCLENCHÉ — ACCÈS NON AUTORISÉ ENREGISTRÉ</div>
            <div style="text-align:center; margin-bottom:16px;">
                <span class="classified">🔒 CONFIDENTIEL — USAGE INTERNE UNIQUEMENT</span>
            </div>
            <h1>RAPPORT FINANCIER Q4 2024<br><small style="font-size:13px;">Données Stratégiques — Ne pas diffuser</small></h1>

            <h2>1. Résultats Financiers</h2>
            <p>Ce document contient des informations financières sensibles de la société. Tout accès non autorisé est contraire à la politique de sécurité et constitue une infraction pénale.</p>

            <table>
                <thead><tr><th>Indicateur</th><th>Q3 2024</th><th>Q4 2024</th><th>Δ</th></tr></thead>
                <tbody>
                    <tr><td>Chiffre d'affaires</td><td>{{ number_format(rand(4000000,8000000)) }} €</td><td>{{ number_format(rand(5000000,10000000)) }} €</td><td style="color:green;">+{{ rand(5,25) }}%</td></tr>
                    <tr><td>EBITDA</td><td>{{ number_format(rand(800000,2000000)) }} €</td><td>{{ number_format(rand(1000000,2500000)) }} €</td><td style="color:green;">+{{ rand(3,18) }}%</td></tr>
                    <tr><td>Trésorerie</td><td colspan="2" style="color:#cc0000; font-style:italic;">[ACCÈS REFUSÉ — HONEYPOT]</td><td>—</td></tr>
                </tbody>
            </table>

            <h2>2. Avertissement de Sécurité</h2>
            <p style="color:#cc0000; font-weight:bold;">
                Votre adresse IP, navigateur, heure d'accès et localisation ont été enregistrés et transmis à notre équipe de sécurité (CSIRT). Si vous avez accédé à ce document par erreur, contactez immédiatement security@company.com.
            </p>

            <p style="margin-top:20px; font-size:11px; color:#999; text-align:center;">
                CyberGuard Canary Token v2.0 — Incident ID: {{ strtoupper(substr(md5(request()->ip()), 0, 12)) }}
            </p>
        </div>
    </div>
</body>
</html>
