<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ module }} module for WTD Core applications.">
    <title>{{ module }} Module | WTD Core</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f7f9fc;
            --panel: #ffffff;
            --ink: #102032;
            --muted: #64748b;
            --line: #dbe3ec;
            --blue: #2563eb;
            --cyan: #0891b2;
            --green: #15803d;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        * { box-sizing: border-box; }
        body { margin: 0; background: var(--bg); color: var(--ink); }
        a { color: inherit; text-decoration: none; }
        .wrap { width: min(1120px, calc(100% - 32px)); margin: 0 auto; }
        .nav { border-bottom: 1px solid var(--line); background: rgba(255, 255, 255, .92); }
        .nav-row { min-height: 68px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 800; color: #172554; }
        .mark { width: 36px; height: 36px; display: grid; place-items: center; border-radius: 8px; color: #fff; background: linear-gradient(135deg, var(--blue), var(--cyan)); }
        .links { display: flex; flex-wrap: wrap; gap: 10px; color: var(--muted); font-size: 14px; font-weight: 700; }
        .links a { padding: 8px 10px; border-radius: 8px; }
        .links a:hover { background: #eef6ff; color: var(--blue); }
        .hero { padding: 72px 0 46px; }
        .hero-grid { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 32px; align-items: center; }
        .kicker { width: fit-content; display: inline-flex; align-items: center; min-height: 32px; padding: 0 12px; border-radius: 999px; border: 1px solid #bfdbfe; background: #eff6ff; color: #1d4ed8; font-size: 13px; font-weight: 800; }
        h1 { margin: 18px 0 14px; font-size: clamp(38px, 7vw, 68px); line-height: 1.04; letter-spacing: 0; }
        .lead { max-width: 680px; margin: 0; color: var(--muted); font-size: 18px; line-height: 1.7; }
        .actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 26px; }
        .button { min-height: 46px; display: inline-flex; align-items: center; justify-content: center; padding: 0 16px; border-radius: 8px; border: 1px solid var(--line); background: #fff; font-weight: 800; }
        .button.primary { border-color: var(--blue); background: var(--blue); color: #fff; }
        .panel { border: 1px solid var(--line); border-radius: 8px; background: var(--panel); padding: 22px; box-shadow: 0 18px 46px rgba(16, 32, 50, .08); }
        .panel h2 { margin: 0; font-size: 22px; }
        .status { display: grid; gap: 12px; margin-top: 16px; }
        .status div { display: flex; justify-content: space-between; gap: 16px; padding: 10px 0; border-bottom: 1px solid var(--line); color: var(--muted); }
        .status strong { color: var(--green); }
        .section { padding: 48px 0; }
        .section h2 { margin: 0 0 18px; font-size: clamp(26px, 4vw, 38px); letter-spacing: 0; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .card { border: 1px solid var(--line); border-radius: 8px; background: var(--panel); padding: 20px; }
        .card span { width: 38px; height: 38px; display: grid; place-items: center; border-radius: 8px; color: #fff; background: var(--blue); font-weight: 900; }
        .card h3 { margin: 16px 0 8px; font-size: 18px; }
        .card p { margin: 0; color: var(--muted); line-height: 1.6; }
        footer { border-top: 1px solid var(--line); padding: 22px 0; color: var(--muted); background: #fff; font-size: 14px; }

        @media (max-width: 820px) {
            .nav-row { align-items: flex-start; flex-direction: column; padding: 14px 0; }
            .hero-grid, .grid { grid-template-columns: 1fr; }
            .hero { padding-top: 44px; }
        }
    </style>
</head>
<body>
    <header class="nav">
        <div class="wrap nav-row">
            <a class="brand" href="/">
                <span class="mark">B</span>
                <span>{{ module }} Module</span>
            </a>
            <nav class="links" aria-label="{{ module }} module navigation">
                <a href="/">Home</a>
                <a href="/health">Health</a>
                <a href="/docs/api">API Docs</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="wrap hero-grid">
                <div>
                    <span class="kicker">WTD Core module</span>
                    <h1>{{ module }} is ready.</h1>
                    <p class="lead">This generated module includes routing, controller dispatch, middleware, model, migration, seeder, tests, and a polished view foundation for real application work.</p>
                    <div class="actions">
                        <a class="button primary" href="/docs/api">Open API Docs</a>
                        <a class="button" href="/health">Check Runtime</a>
                    </div>
                </div>
                <aside class="panel" aria-label="{{ module }} module status">
                    <h2>Module status</h2>
                    <div class="status">
                        <div><span>Route</span><strong>registered</strong></div>
                        <div><span>Controller</span><strong>ready</strong></div>
                        <div><span>Views</span><strong>designed</strong></div>
                        <div><span>Tests</span><strong>generated</strong></div>
                    </div>
                </aside>
            </div>
        </section>

        <section class="section">
            <div class="wrap">
                <h2>Build from a complete structure.</h2>
                <div class="grid">
                    <article class="card">
                        <span>R</span>
                        <h3>Routes and controllers</h3>
                        <p>Start with a web route and controller that renders through the framework view service.</p>
                    </article>
                    <article class="card">
                        <span>D</span>
                        <h3>Data layer</h3>
                        <p>Use the generated model, migration, and seeder as the module's first persistence boundary.</p>
                    </article>
                    <article class="card">
                        <span>V</span>
                        <h3>View foundation</h3>
                        <p>Layout, page, partial, and component templates are included so the UI starts clean.</p>
                    </article>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="wrap">{{ module }} Module for WTD Core by Web Tech Domains.</div>
    </footer>
</body>
</html>
