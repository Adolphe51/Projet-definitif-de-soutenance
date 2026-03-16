<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>phpMyAdmin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f8f9fa; font-family: Arial, sans-serif; }
        .header {
            background: #3c4248;
            padding: 10px 20px;
            display: flex; align-items: center; gap: 12px;
        }
        .header .logo { color: #f97316; font-size: 24px; font-weight: bold; }
        .header .version { color: #9ca3af; font-size: 12px; }
        .main { display: flex; height: calc(100vh - 50px); }
        .sidebar {
            width: 220px;
            background: #fff;
            border-right: 1px solid #dee2e6;
            padding: 16px 0;
        }
        .sidebar-title { font-size: 11px; color: #6c757d; text-transform: uppercase; padding: 0 16px 8px; font-weight: bold; letter-spacing: 1px; }
        .sidebar-item {
            display: flex; align-items: center; gap: 8px;
            padding: 7px 16px;
            font-size: 13px;
            color: #495057;
            cursor: pointer;
            border-left: 3px solid transparent;
        }
        .sidebar-item:hover { background: #f8f9fa; border-left-color: #3c4248; }
        .content { flex: 1; padding: 20px; display: flex; align-items: center; justify-content: center; }
        .login-box {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 32px;
            width: 420px;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
        }
        .login-box h2 { font-size: 18px; margin-bottom: 6px; color: #1a1a2e; }
        .login-box p  { font-size: 13px; color: #6c757d; margin-bottom: 24px; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px; color: #374151; }
        input[type="text"], input[type="password"], select {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            padding: 9px 11px;
            font-size: 14px;
            margin-bottom: 14px;
            outline: none;
        }
        input:focus, select:focus { border-color: #3c4248; box-shadow: 0 0 0 2px rgba(60,66,72,0.2); }
        .btn {
            background: #3c4248;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 24px;
            font-size: 14px;
            cursor: pointer;
            width: 100%;
            margin-top: 4px;
        }
        .btn:hover { background: #2c3238; }
        .error-msg { background: #fee2e2; border: 1px solid #fca5a5; border-radius: 4px; padding: 10px; font-size: 13px; color: #991b1b; margin-bottom: 16px; }
        .footer { text-align: center; font-size: 11px; color: #9ca3af; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">phpMyAdmin</div>
        <div class="version">5.2.1</div>
    </div>

    <div class="main">
        <div class="sidebar">
            <div class="sidebar-title">Databases</div>
            @foreach(['information_schema','mysql','performance_schema','production_db','users_backup'] as $db)
            <div class="sidebar-item">🗄️ {{ $db }}</div>
            @endforeach
        </div>

        <div class="content">
            <div class="login-box">
                <h2>Welcome to phpMyAdmin</h2>
                <p>Please enter your MySQL server credentials.</p>

                @if(session('error'))
                <div class="error-msg">⚠️ Access Denied for user. Please check your credentials.</div>
                @endif

                <form method="POST" action="{{ route('honeypot.trap.pma') }}">
                    @csrf
                    <div>
                        <label>Username</label>
                        <input type="text" name="username" placeholder="root" autocomplete="off">
                    </div>
                    <div>
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Enter password">
                    </div>
                    <div>
                        <label>Server</label>
                        <select name="server">
                            <option value="1">127.0.0.1 (Local)</option>
                            <option value="2">10.0.0.5 (Production)</option>
                            <option value="3">10.0.0.10 (Backup)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn">Go →</button>
                </form>
                <div class="footer">phpMyAdmin 5.2.1 — MySQL 8.0.32</div>
            </div>
        </div>
    </div>
</body>
</html>
