<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ name }}</title>
    <style>
        :root { color-scheme: light; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        body { margin: 0; background: #f7f9fc; color: #111827; }
        main { min-height: 100vh; display: grid; place-items: center; padding: 32px; }
        section { width: min(960px, 100%); }
        h1 { margin: 0 0 16px; font-size: clamp(40px, 8vw, 84px); line-height: 1; letter-spacing: 0; }
        p { max-width: 680px; margin: 0 0 28px; color: #4b5563; font-size: 18px; line-height: 1.7; }
        nav { display: flex; flex-wrap: wrap; gap: 12px; }
        a { color: #0f766e; font-weight: 700; text-decoration: none; border-bottom: 2px solid transparent; }
        a:hover { border-color: currentColor; }
    </style>
</head>
<body>
    <main>
        <section>
            <h1>{{ name }}</h1>
            <p>{{ description }}</p>
            <nav aria-label="Project links">
                <a href="/health">Health</a>
                <a href="/api/status">API Status</a>
                <a href="/docs/api">API Docs</a>
            </nav>
        </section>
    </main>
</body>
</html>
