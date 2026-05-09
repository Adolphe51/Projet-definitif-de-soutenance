<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access denied</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #0f172a;
            color: #e2e8f0;
            font-family: Arial, sans-serif;
        }

        .panel {
            width: min(560px, calc(100% - 32px));
            padding: 32px;
            border: 1px solid rgba(248, 113, 113, 0.35);
            border-radius: 16px;
            background: rgba(15, 23, 42, 0.92);
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.45);
        }

        h1 {
            margin: 0 0 12px;
            font-size: 1.5rem;
            color: #f87171;
        }

        p {
            margin: 0;
            line-height: 1.6;
            color: #cbd5e1;
        }
    </style>
</head>
<body>
    <section class="panel">
        <h1>Access denied</h1>
        <p>{{ $message ?? 'Your request could not be processed.' }}</p>
    </section>
</body>
</html>
