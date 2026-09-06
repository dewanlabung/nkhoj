<!DOCTYPE html>
<html lang="ne">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>nkhoj — नेपाली बहु-ब्लग प्लेटफर्म</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { text-align: center; max-width: 480px; padding: 2.5rem; }
        h1 { font-size: 3rem; font-weight: 800; background: linear-gradient(135deg, #6366f1, #a855f7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: .5rem; }
        p { color: #94a3b8; font-size: 1.1rem; margin-bottom: 2rem; }
        .badge { display: inline-block; background: #1e293b; border: 1px solid #334155; border-radius: 999px; padding: .4rem 1rem; font-size: .85rem; color: #64748b; }
        .status { margin-top: 2rem; padding: 1rem; background: #16a34a22; border: 1px solid #16a34a55; border-radius: .75rem; color: #4ade80; font-size: .9rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1>nkhoj</h1>
        <p>नेपाली समाचार र ब्लग प्लेटफर्म</p>
        <div class="badge">Laravel {{ app()->version() }} · PHP {{ PHP_VERSION }}</div>
        <div class="status">✓ Application running</div>
    </div>
</body>
</html>
