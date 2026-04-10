<!DOCTYPE html>
<html lang="en" id="html">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Session Expired — Mental Health Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0f172a;
            color: #e2e8f0;
        }
        .card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 24px;
            padding: 3rem;
            max-width: 480px;
            width: 90%;
            text-align: center;
            backdrop-filter: blur(20px);
        }
        .icon { font-size: 4rem; margin-bottom: 1.5rem; }
        h1 { font-size: 1.75rem; font-weight: 900; margin-bottom: 0.75rem; color: #f1f5f9; }
        p { color: #94a3b8; font-size: 1rem; line-height: 1.6; margin-bottom: 2rem; }
        .btn {
            display: inline-block;
            background: #0d9488;
            color: white;
            padding: 0.85rem 2.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .btn:hover { background: #0f766e; transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">⏱️</div>
        <h1>Session Expired</h1>
        <p>Your session has timed out for security reasons. Please log in again to continue using the portal.</p>
        <a href="/login" class="btn">Return to Login →</a>
    </div>
    <script>
        // Auto-redirect after 5 seconds
        setTimeout(() => { window.location.href = '/login'; }, 5000);
    </script>
</body>
</html>
