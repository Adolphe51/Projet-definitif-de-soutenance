{{-- fake_admin_panel.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel — System Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #1a1a2e; color: #e0e0e0; font-family: Arial, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-wrap { width: 380px; }
        .logo { text-align: center; margin-bottom: 28px; }
        .logo h1 { font-size: 24px; color: #4fc3f7; letter-spacing: 2px; }
        .logo p  { font-size: 12px; color: #607d8b; margin-top: 4px; }
        .card { background: #16213e; border: 1px solid #0f3460; border-radius: 8px; padding: 28px; }
        .field-group { margin-bottom: 16px; }
        label { display: block; font-size: 12px; color: #607d8b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
        input { width: 100%; background: #0f3460; border: 1px solid #1a4080; border-radius: 5px; color: #e0e0e0; padding: 10px 12px; font-size: 14px; outline: none; }
        input:focus { border-color: #4fc3f7; }
        .btn { width: 100%; background: linear-gradient(135deg, #4fc3f7, #0288d1); color: #fff; border: none; border-radius: 5px; padding: 12px; font-size: 14px; font-weight: bold; cursor: pointer; letter-spacing: 1px; margin-top: 8px; }
        .btn:hover { opacity: 0.9; }
        .hint { text-align: center; font-size: 11px; color: #37474f; margin-top: 16px; }
        @if(session('error'))
        .error { background: rgba(244,67,54,0.1); border: 1px solid #ef5350; border-radius: 4px; padding: 10px; font-size: 13px; color: #ef5350; margin-bottom: 16px; text-align: center; }
        @endif
    </style>
</head>
<body>
    <div class="login-wrap">
        <div class="logo">
            <h1>⚙️ ADMIN PANEL</h1>
            <p>SYSTEM MANAGEMENT CONSOLE v3.2</p>
        </div>
        <div class="card">
            @if(session('error'))
            <div class="error">⚠ {{ session('error') }}</div>
            @endif
            <form method="POST" action="{{ route('honeypot.trap.admin') }}">
                @csrf
                <div class="field-group">
                    <label>Admin Username</label>
                    <input type="text" name="username" placeholder="administrator" autocomplete="off">
                </div>
                <div class="field-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••••">
                </div>
                <button type="submit" class="btn">ACCESS SYSTEM</button>
            </form>
            <div class="hint">Unauthorized access is prohibited and monitored</div>
        </div>
    </div>
</body>
</html>
