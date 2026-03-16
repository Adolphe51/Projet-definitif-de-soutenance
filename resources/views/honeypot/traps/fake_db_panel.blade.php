{{-- fake_db_panel.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>phpMyAdmin — production_db</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background:#f5f5f5; font-family:Arial,sans-serif; font-size:13px; }
        .nav { background:#3c4248; color:#fff; padding:6px 12px; display:flex; gap:16px; align-items:center; }
        .nav a { color:#ccc; text-decoration:none; font-size:12px; }
        .nav a:hover { color:#fff; }
        .content { padding:16px; }
        .sql-box { background:#fff; border:1px solid #ddd; border-radius:4px; padding:12px; margin-bottom:12px; }
        .sql-box textarea { width:100%; height:80px; border:1px solid #ccc; border-radius:3px; padding:8px; font-family:monospace; font-size:12px; resize:vertical; }
        table { width:100%; border-collapse:collapse; background:#fff; border:1px solid #ddd; }
        th { background:#3c4248; color:#fff; padding:8px 10px; text-align:left; font-size:12px; }
        td { padding:7px 10px; border-bottom:1px solid #eee; font-size:12px; }
        tr:hover td { background:#f0f7ff; }
        .btn { background:#3c4248; color:#fff; border:none; padding:6px 14px; border-radius:3px; cursor:pointer; font-size:12px; }
        .warn { background:#fff3cd; border:1px solid #ffc107; padding:10px; border-radius:4px; margin-bottom:12px; font-size:12px; color:#856404; }
    </style>
</head>
<body>
    <div class="nav">
        <strong style="color:#f97316">phpMyAdmin</strong>
        <a href="#">Server: 127.0.0.1</a>
        <a href="#">Database: production_db</a>
        <a href="#" style="color:#f97316">⚠ HONEYPOT — IP Logged</a>
    </div>
    <div class="content">
        <div class="warn">⚠️ <strong>SECURITY NOTICE:</strong> This is a monitored honeypot environment. Your IP, credentials, and all actions have been logged and reported to the security team.</div>
        <div class="sql-box">
            <div style="font-size:11px;color:#6c757d;margin-bottom:6px;">SQL Query</div>
            <textarea>SELECT * FROM users LIMIT 100;</textarea>
            <br><br><button class="btn">Go</button>
        </div>
        <table>
            <thead><tr><th>id</th><th>username</th><th>email</th><th>password_hash</th><th>role</th></tr></thead>
            <tbody>
                @foreach(\App\Services\HoneypotService::getFakeDataset('users_db') as $u)
                <tr>
                    <td>{{ $u['id'] }}</td>
                    <td>{{ $u['username'] }}</td>
                    <td>{{ $u['email'] }}</td>
                    <td style="font-family:monospace; font-size:11px; color:#666;">$2y$10${{ substr(md5($u['username']),0,50) }}...</td>
                    <td>{{ $u['role'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
