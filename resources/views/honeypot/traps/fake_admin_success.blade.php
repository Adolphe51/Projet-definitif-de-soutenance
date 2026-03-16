{{-- fake_admin_success.blade.php - Page fausse "succès" après soumission creds --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel — Dashboard</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background:#1a1a2e; color:#e0e0e0; font-family:Arial,sans-serif; min-height:100vh; }
        .topbar { background:#16213e; padding:12px 24px; display:flex; align-items:center; gap:12px; border-bottom:1px solid #0f3460; }
        .topbar h1 { font-size:16px; color:#4fc3f7; letter-spacing:1px; }
        .topbar .user { margin-left:auto; font-size:13px; color:#607d8b; }
        .main { padding:24px; }
        .grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px; }
        .card { background:#16213e; border:1px solid #0f3460; border-radius:8px; padding:18px; }
        .card h3 { font-size:28px; color:#4fc3f7; }
        .card p  { font-size:12px; color:#607d8b; margin-top:4px; }
        table { width:100%; border-collapse:collapse; }
        th { text-align:left; padding:10px 12px; font-size:11px; color:#607d8b; text-transform:uppercase; border-bottom:1px solid #0f3460; }
        td { padding:10px 12px; font-size:13px; border-bottom:1px solid rgba(15,52,96,0.4); }
        .banner { background:rgba(244,67,54,0.08); border:1px solid rgba(244,67,54,0.3); border-radius:8px; padding:16px; margin-bottom:20px; font-size:13px; color:#ef9a9a; }
    </style>
</head>
<body>
    <div class="topbar">
        <h1>⚙️ ADMIN PANEL</h1>
        <span class="user">Welcome, admin | Last login: {{ now()->subMinutes(rand(5,60))->format('H:i') }}</span>
    </div>
    <div class="main">
        <div class="banner">
            ⚠️ <strong>SECURITY ALERT:</strong> Your credentials have been captured and your IP address has been logged. This is a honeypot system. This incident has been reported.
        </div>
        <div class="grid">
            <div class="card"><h3>{{ rand(1200,3500) }}</h3><p>Total Users</p></div>
            <div class="card"><h3>{{ rand(50,200) }}</h3><p>Active Sessions</p></div>
            <div class="card"><h3>{{ rand(10,50) }} GB</h3><p>Database Size</p></div>
            <div class="card"><h3>{{ rand(85,99) }}%</h3><p>System Uptime</p></div>
        </div>
        <div class="card">
            <table>
                <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Last Login</th></tr></thead>
                <tbody>
                    @foreach(\App\Services\HoneypotService::getFakeDataset('users_db') as $user)
                    <tr>
                        <td style="color:#607d8b;">{{ $user['id'] }}</td>
                        <td style="color:#4fc3f7;">{{ $user['username'] }}</td>
                        <td>{{ $user['email'] }}</td>
                        <td><span style="background:rgba(79,195,247,0.1);color:#4fc3f7;padding:2px 8px;border-radius:3px;font-size:11px;">{{ $user['role'] }}</span></td>
                        <td style="color:#607d8b;font-size:12px;">{{ $user['last_login'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
