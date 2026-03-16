{{-- fake_error.blade.php --}}
<!DOCTYPE html>
<html>
<head><title>403 Forbidden</title>
<style>
    body { background:#1a1a1a; color:#e0e0e0; font-family:monospace; display:flex; align-items:center; justify-content:center; min-height:100vh; text-align:center; }
    .box { padding:40px; }
    h1 { font-size:80px; color:#ff4444; margin-bottom:8px; }
    h2 { font-size:20px; color:#ff8888; margin-bottom:16px; }
    p  { color:#888; font-size:14px; line-height:2; }
    .ip { color:#00e5ff; }
    .log-id { background:#333; padding:4px 10px; border-radius:4px; font-size:12px; color:#ffd600; }
</style>
</head>
<body>
    <div class="box">
        <h1>403</h1>
        <h2>⚠ Access Forbidden</h2>
        <p>Your IP address <span class="ip">{{ request()->ip() }}</span> has been flagged.</p>
        <p>{{ $message ?? 'Authentication failed. This attempt has been logged.' }}</p>
        <p>Incident Reference: <span class="log-id">CG-{{ strtoupper(substr(md5(now()->timestamp), 0, 8)) }}</span></p>
    </div>
</body>
</html>
