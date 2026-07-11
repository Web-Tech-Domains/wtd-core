<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Blog module for WTD Core applications.">
    <title>Blog Module | WTD Core</title>
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
        .module-shell { min-height: 100vh; display: flex; flex-direction: column; }
        .module-wrap { width: min(1120px, calc(100% - 32px)); margin: 0 auto; }
        .module-nav { border-bottom: 1px solid var(--line); background: rgba(255, 255, 255, .92); }
        .module-nav-row { min-height: 68px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .module-brand { display: flex; align-items: center; gap: 10px; font-weight: 800; color: #172554; }
        .module-mark { width: 36px; height: 36px; display: grid; place-items: center; border-radius: 8px; color: #fff; background: linear-gradient(135deg, var(--blue), var(--cyan)); }
        .module-links { display: flex; flex-wrap: wrap; gap: 10px; color: var(--muted); font-size: 14px; font-weight: 700; }
        .module-links a { padding: 8px 10px; border-radius: 8px; }
        .module-links a:hover { background: #eef6ff; color: var(--blue); }
        .module-main { flex: 1; }
        .module-hero { padding: 72px 0 46px; }
        .module-hero-grid { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 32px; align-items: center; }
        .module-kicker { width: fit-content; display: inline-flex; align-items: center; min-height: 32px; padding: 0 12px; border-radius: 999px; border: 1px solid #bfdbfe; background: #eff6ff; color: #1d4ed8; font-size: 13px; font-weight: 800; }
        .module-title { margin: 18px 0 14px; font-size: clamp(38px, 7vw, 68px); line-height: 1.04; letter-spacing: 0; }
        .module-lead { max-width: 680px; margin: 0; color: var(--muted); font-size: 18px; line-height: 1.7; }
        .module-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 26px; }
        .module-button { min-height: 46px; display: inline-flex; align-items: center; justify-content: center; padding: 0 16px; border-radius: 8px; border: 1px solid var(--line); background: #fff; font-weight: 800; }
        .module-button.primary { border-color: var(--blue); background: var(--blue); color: #fff; }
        .module-panel { border: 1px solid var(--line); border-radius: 8px; background: var(--panel); padding: 22px; box-shadow: 0 18px 46px rgba(16, 32, 50, .08); }
        .module-status { display: grid; gap: 12px; margin-top: 16px; }
        .module-status div { display: flex; justify-content: space-between; gap: 16px; padding: 10px 0; border-bottom: 1px solid var(--line); color: var(--muted); }
        .module-status strong { color: var(--green); }
        .module-section { padding: 48px 0; }
        .module-section h2 { margin: 0 0 18px; font-size: clamp(26px, 4vw, 38px); letter-spacing: 0; }
        .module-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .module-card { border: 1px solid var(--line); border-radius: 8px; background: var(--panel); padding: 20px; }
        .module-card span { width: 38px; height: 38px; display: grid; place-items: center; border-radius: 8px; color: #fff; background: var(--blue); font-weight: 900; }
        .module-card h3 { margin: 16px 0 8px; font-size: 18px; }
        .module-card p { margin: 0; color: var(--muted); line-height: 1.6; }
        .module-footer { border-top: 1px solid var(--line); padding: 22px 0; color: var(--muted); background: #fff; font-size: 14px; }

        @media (max-width: 820px) {
            .module-nav-row { align-items: flex-start; flex-direction: column; padding: 14px 0; }
            .module-hero-grid, .module-grid { grid-template-columns: 1fr; }
            .module-hero { padding-top: 44px; }
        }
    </style>
</head>
<body>
    <div class="module-shell">
        {{ content }}
    </div>
</body>
</html>
