<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In ‹ MyCompany — WordPress</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #f0f0f1;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            min-height: 100vh;
        }
        .wp-logo { margin-bottom: 24px; text-align: center; }
        .wp-logo svg { width: 84px; height: 84px; }
        .login-container {
            background: #fff;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            padding: 26px 24px 34px;
            width: 320px;
            box-shadow: 0 1px 3px rgba(0,0,0,.1);
        }
        label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 5px; color: #1d2327; }
        input[type="text"], input[type="password"] {
            width: 100%;
            border: 1px solid #8c8f94;
            border-radius: 4px;
            padding: 8px;
            font-size: 15px;
            margin-bottom: 16px;
            outline: none;
        }
        input:focus { border-color: #2271b1; box-shadow: 0 0 0 1px #2271b1; }
        .submit-btn {
            background: #2271b1;
            color: white;
            border: none;
            border-radius: 3px;
            padding: 8px 20px;
            font-size: 14px;
            cursor: pointer;
            float: right;
        }
        .submit-btn:hover { background: #135e96; }
        .form-group { clear: both; }
        a { color: #2271b1; text-decoration: none; font-size: 13px; }
        a:hover { color: #135e96; text-decoration: underline; }
        .nav-links { text-align: center; margin-top: 16px; }
        .nav-links a { margin: 0 8px; color: #50575e; }
        .error-msg {
            background: #fce8e8;
            border-left: 4px solid #cc1818;
            padding: 10px 14px;
            margin-bottom: 16px;
            font-size: 13px;
            color: #1d2327;
            border-radius: 0 4px 4px 0;
        }
        .remember { display: flex; align-items: center; gap: 6px; margin-bottom: 16px; font-size: 14px; }
        /* SECRET: honeypot indicator - only visible to admin */
        .hp-notice {
            position: fixed; bottom: 4px; right: 4px;
            background: rgba(0,0,0,0.05);
            color: transparent;
            font-size: 8px;
            padding: 2px 4px;
            border-radius: 2px;
            user-select: none;
        }
    </style>
</head>
<body>
    <div class="wp-logo">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="-0.5 -0.5 101 101">
            <circle cx="50" cy="50" r="50" fill="#00749C"/>
            <path d="M50 9.9C27.8 9.9 9.9 27.8 9.9 50S27.8 90.1 50 90.1 90.1 72.2 90.1 50 72.2 9.9 50 9.9zm0 3c10.4 0 19.8 3.9 26.9 10.3L16.2 76.9C12.4 69.9 10.1 61.7 10.1 53c0-8.6 2.3-16.7 6.3-23.7L83.7 86.2C76.7 91.9 67.8 95 58 95c-8.6 0-16.7-2.3-23.7-6.3l73.6-26c1.6-4.8 2.6-9.9 2.6-15.2 0-19.3-10.9-36.2-26.9-44.6z" fill="white"/>
        </svg>
    </div>

    <div class="login-container">
        @if(session('error'))
        <div class="error-msg"><strong>ERROR:</strong> {{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('honeypot.trap.wp') }}">
            @csrf
            <div>
                <label for="log">Username or Email Address</label>
                <input type="text" name="log" id="log" autocomplete="username" required>
            </div>
            <div>
                <label for="pwd">Password</label>
                <input type="password" name="pwd" id="pwd" autocomplete="current-password" required>
            </div>
            <div class="remember">
                <input type="checkbox" name="rememberme" id="rememberme" value="forever">
                <label for="rememberme" style="margin-bottom:0;">Remember Me</label>
            </div>
            <div class="form-group">
                <input type="hidden" name="redirect_to" value="/wp-admin/">
                <button type="submit" class="submit-btn">Log In</button>
            </div>
        </form>
    </div>

    <div class="nav-links">
        <a href="#">Lost your password?</a>
        <span>←</span>
        <a href="#">Go to MyCompany</a>
    </div>

    <!-- HONEYPOT MARKER - invisible to attackers -->
    <div class="hp-notice" title="CYBERGUARD HONEYPOT v2.0">HP</div>
</body>
</html>
