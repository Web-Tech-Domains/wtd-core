<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ description }}">
    <title>{{ name }} | Web Tech Domains</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f6f8fb;
            --panel: #ffffff;
            --ink: #102032;
            --muted: #5e6d7c;
            --line: #dbe3ec;
            --blue: #2563eb;
            --cyan: #0891b2;
            --green: #15803d;
            --amber: #b45309;
            --navy: #172554;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        * { box-sizing: border-box; }
        body { margin: 0; background: var(--bg); color: var(--ink); }
        a { color: inherit; text-decoration: none; }
        .wrap { width: min(1180px, calc(100% - 40px)); margin: 0 auto; }
        .topbar { border-bottom: 1px solid var(--line); background: rgba(255, 255, 255, .9); position: sticky; top: 0; z-index: 5; backdrop-filter: blur(16px); }
        .nav { min-height: 72px; display: flex; align-items: center; justify-content: space-between; gap: 20px; }
        .brand { display: flex; align-items: center; gap: 12px; font-weight: 800; color: var(--navy); }
        .mark { width: 38px; height: 38px; border-radius: 8px; display: grid; place-items: center; color: #fff; background: linear-gradient(135deg, var(--blue), var(--cyan)); box-shadow: 0 12px 30px rgba(37, 99, 235, .22); }
        .navlinks { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: flex-end; }
        .navlinks a { min-height: 38px; display: inline-flex; align-items: center; padding: 0 14px; border: 1px solid transparent; border-radius: 8px; color: var(--muted); font-weight: 700; font-size: 14px; }
        .navlinks a:hover { border-color: var(--line); color: var(--ink); background: #fff; }

        .hero { padding: 74px 0 44px; }
        .hero-grid { display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(320px, .95fr); gap: 44px; align-items: center; }
        .eyebrow { width: fit-content; display: inline-flex; align-items: center; gap: 8px; min-height: 34px; padding: 0 12px; border: 1px solid #bfdbfe; border-radius: 999px; background: #eff6ff; color: #1d4ed8; font-size: 13px; font-weight: 800; }
        .dot { width: 8px; height: 8px; border-radius: 999px; background: var(--green); }
        h1 { margin: 18px 0 18px; max-width: 760px; font-size: clamp(42px, 7vw, 78px); line-height: 1.02; letter-spacing: 0; color: #071424; }
        .lead { max-width: 710px; margin: 0; color: var(--muted); font-size: 19px; line-height: 1.75; }
        .actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 30px; }
        .btn { min-height: 48px; display: inline-flex; align-items: center; justify-content: center; gap: 10px; padding: 0 18px; border-radius: 8px; font-weight: 800; border: 1px solid var(--line); background: #fff; }
        .btn.primary { border-color: var(--blue); background: var(--blue); color: #fff; box-shadow: 0 16px 34px rgba(37, 99, 235, .22); }
        .btn:hover { transform: translateY(-1px); }
        .signals { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 26px; color: var(--muted); font-size: 14px; font-weight: 700; }
        .signals span { padding: 8px 10px; border: 1px solid var(--line); border-radius: 8px; background: rgba(255, 255, 255, .72); }

        .terminal { border: 1px solid #cbd5e1; border-radius: 8px; background: #0f172a; color: #dbeafe; box-shadow: 0 28px 70px rgba(15, 23, 42, .24); overflow: hidden; }
        .terminal-head { height: 44px; display: flex; align-items: center; gap: 8px; padding: 0 16px; background: #111827; border-bottom: 1px solid rgba(255, 255, 255, .08); }
        .terminal-head span { width: 10px; height: 10px; border-radius: 999px; background: #ef4444; }
        .terminal-head span:nth-child(2) { background: #f59e0b; }
        .terminal-head span:nth-child(3) { background: #22c55e; }
        .terminal-body { padding: 22px; font-family: "SFMono-Regular", Consolas, "Liberation Mono", monospace; font-size: 14px; line-height: 1.8; min-height: 360px; }
        .muted-code { color: #93a4b8; }
        .ok { color: #86efac; }
        .info { color: #67e8f9; }
        .warn { color: #fcd34d; }

        .band { padding: 24px 0; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); background: #fff; }
        .stack { display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; }
        .stack span { min-height: 48px; display: grid; place-items: center; border: 1px solid var(--line); border-radius: 8px; color: var(--muted); font-weight: 800; background: #fbfdff; }

        .section { padding: 68px 0; }
        .section-head { display: flex; align-items: end; justify-content: space-between; gap: 24px; margin-bottom: 24px; }
        h2 { margin: 0; font-size: clamp(28px, 4vw, 44px); line-height: 1.12; letter-spacing: 0; }
        .section-head p { max-width: 560px; margin: 0; color: var(--muted); line-height: 1.7; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        .card { border: 1px solid var(--line); border-radius: 8px; background: var(--panel); padding: 22px; box-shadow: 0 12px 34px rgba(16, 32, 50, .06); }
        .icon { width: 42px; height: 42px; display: grid; place-items: center; border-radius: 8px; margin-bottom: 18px; color: #fff; font-weight: 900; background: var(--blue); }
        .icon.cyan { background: var(--cyan); }
        .icon.green { background: var(--green); }
        .icon.amber { background: var(--amber); }
        .card h3 { margin: 0 0 10px; font-size: 19px; }
        .card p { margin: 0; color: var(--muted); line-height: 1.65; }
        .card ul { margin: 16px 0 0; padding: 0; list-style: none; display: grid; gap: 8px; color: #334155; font-size: 14px; }
        .card li::before { content: "✓"; margin-right: 8px; color: var(--green); font-weight: 900; }

        .split { display: grid; grid-template-columns: .9fr 1.1fr; gap: 22px; align-items: stretch; }
        .product-panel { min-height: 100%; border-radius: 8px; padding: 28px; background: linear-gradient(135deg, #102032, #1e3a8a); color: #fff; }
        .product-panel p { color: #cbd5e1; line-height: 1.7; }
        .metrics { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-top: 22px; }
        .metric { border: 1px solid rgba(255, 255, 255, .16); border-radius: 8px; padding: 16px; background: rgba(255, 255, 255, .08); }
        .metric strong { display: block; font-size: 27px; }
        .metric span { color: #cbd5e1; font-size: 13px; }
        .timeline { display: grid; gap: 12px; }
        .step { display: grid; grid-template-columns: 42px 1fr; gap: 14px; align-items: start; border: 1px solid var(--line); border-radius: 8px; background: #fff; padding: 18px; }
        .num { width: 42px; height: 42px; display: grid; place-items: center; border-radius: 8px; color: #fff; background: var(--navy); font-weight: 900; }
        .step h3 { margin: 0 0 6px; font-size: 17px; }
        .step p { margin: 0; color: var(--muted); line-height: 1.6; }

        .cta { padding: 42px 0 70px; }
        .cta-box { display: flex; align-items: center; justify-content: space-between; gap: 24px; border-radius: 8px; padding: 30px; background: #071424; color: #fff; }
        .cta-box p { max-width: 680px; margin: 8px 0 0; color: #cbd5e1; line-height: 1.65; }

        @media (max-width: 900px) {
            .nav { align-items: flex-start; flex-direction: column; padding: 14px 0; }
            .navlinks { justify-content: flex-start; }
            .hero-grid, .split { grid-template-columns: 1fr; }
            .stack { grid-template-columns: repeat(3, 1fr); }
            .grid { grid-template-columns: repeat(2, 1fr); }
            .section-head, .cta-box { align-items: flex-start; flex-direction: column; }
        }

        @media (max-width: 620px) {
            .wrap { width: min(100% - 28px, 1180px); }
            .hero { padding-top: 44px; }
            .terminal-body { min-height: 300px; padding: 18px; font-size: 12px; }
            .stack, .grid, .metrics { grid-template-columns: 1fr; }
            .btn { width: 100%; }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="wrap nav">
            <a class="brand" href="/">
                <span class="mark">W</span>
                <span>Web Tech Domains / {{ name }}</span>
            </a>
            <nav class="navlinks" aria-label="Primary navigation">
                <a href="/health">Health</a>
                <a href="/api/status">API Status</a>
                <a href="/docs/api">OpenAPI</a>
                <a href="#modules">Modules</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="wrap hero-grid">
                <div>
                    <span class="eyebrow"><span class="dot"></span> Enterprise PHP 8.3 framework by Web Tech Domains</span>
                    <h1>{{ name }}</h1>
                    <p class="lead">{{ description }}</p>
                    <div class="actions">
                        <a class="btn primary" href="/docs/api">Open API Docs</a>
                        <a class="btn" href="/health">Check Runtime</a>
                    </div>
                    <div class="signals" aria-label="Framework standards">
                        <span>PSR-4 / PSR-11 / PSR-15</span>
                        <span>Laravel-style DX</span>
                        <span>Slim-like footprint</span>
                        <span>Symfony-friendly architecture</span>
                    </div>
                </div>

                <div class="terminal" aria-label="Framework capabilities preview">
                    <div class="terminal-head"><span></span><span></span><span></span></div>
                    <div class="terminal-body">
                        <div><span class="muted-code">$</span> php core make:module Billing</div>
                        <div class="ok">✓ module structure created</div>
                        <div><span class="muted-code">$</span> php core make:model Invoice --migration</div>
                        <div class="ok">✓ model, repository, timestamps, migration ready</div>
                        <br>
                        <div><span class="info">Route::middleware</span>(['web', 'auth'])</div>
                        <div><span class="info">Database::connection</span>('tenant')</div>
                        <div><span class="info">Queue::dispatch</span>(new SendInvoice)</div>
                        <div><span class="info">OpenApi::document</span>('public')</div>
                        <br>
                        <div class="warn">Built for SaaS, CRM plugins, AI products, APIs, and enterprise panels.</div>
                    </div>
                </div>
            </div>
        </section>

        <div class="band">
            <div class="wrap stack" aria-label="Supported stack">
                <span>PHP 8.3+</span>
                <span>MySQL</span>
                <span>PostgreSQL</span>
                <span>SQLite</span>
                <span>Redis</span>
                <span>Docker</span>
            </div>
        </div>

        <section class="section" id="modules">
            <div class="wrap">
                <div class="section-head">
                    <h2>Framework foundation for real WTD products.</h2>
                    <p>Web Tech Domains ships web apps, mobile backends, CRM extensions, AI support agents, cloud deployments, and digital platforms. WTD Core packages those needs into a reusable PHP framework.</p>
                </div>
                <div class="grid">
                    <article class="card">
                        <div class="icon">R</div>
                        <h3>Routing & HTTP</h3>
                        <p>Fast route matching, middleware pipelines, PSR-style requests and responses, route caching, and clean controller dispatch.</p>
                        <ul>
                            <li>Web and API routes</li>
                            <li>Controller actions</li>
                            <li>Custom error pages</li>
                        </ul>
                    </article>
                    <article class="card">
                        <div class="icon cyan">M</div>
                        <h3>Modules</h3>
                        <p>Generate complete bounded modules with providers, routes, controllers, models, migrations, seeders, views, middleware, tests, and config.</p>
                        <ul>
                            <li>Package-based development</li>
                            <li>Third-party libraries</li>
                            <li>Reusable feature folders</li>
                        </ul>
                    </article>
                    <article class="card">
                        <div class="icon green">D</div>
                        <h3>Database & ORM</h3>
                        <p>Multiple connection providers, schema blueprints, migrations, seeders, factories, repositories, timestamps, hooks, and model events.</p>
                        <ul>
                            <li>Multiple database drivers</li>
                            <li>Tenant-ready connections</li>
                            <li>Soft deletes and UUIDs</li>
                        </ul>
                    </article>
                    <article class="card">
                        <div class="icon amber">S</div>
                        <h3>Security</h3>
                        <p>Session handling, cookies, CSRF, validation, authorization, hashing, API token guard, encrypted payload support, and hardened headers.</p>
                        <ul>
                            <li>Secure defaults</li>
                            <li>Auth and permissions</li>
                            <li>Input validation</li>
                        </ul>
                    </article>
                    <article class="card">
                        <div class="icon">Q</div>
                        <h3>Queues & Automation</h3>
                        <p>Jobs, batches, database queues, in-memory testing drivers, scheduler primitives, notifications, mail, and event-driven workflows.</p>
                        <ul>
                            <li>Background jobs</li>
                            <li>Scheduled tasks</li>
                            <li>Mail and notifications</li>
                        </ul>
                    </article>
                    <article class="card">
                        <div class="icon cyan">V</div>
                        <h3>Modern Frontend</h3>
                        <p>Vite-ready resources for Vue, React, or classic server-rendered views, keeping API and SaaS products flexible for market-standard UI stacks.</p>
                        <ul>
                            <li>Vite asset pipeline</li>
                            <li>Vue or React friendly</li>
                            <li>View helpers</li>
                        </ul>
                    </article>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="wrap split">
                <div class="product-panel">
                    <span class="eyebrow">Developer Experience</span>
                    <h2>Designed for teams that need to ship and maintain.</h2>
                    <p>WTD Core keeps the development flow direct: generate the feature, wire the route, migrate the schema, test the behavior, document the API, and deploy with Docker or PHP-FPM.</p>
                    <div class="metrics">
                        <div class="metric"><strong>18</strong><span>planned framework phases</span></div>
                        <div class="metric"><strong>231+</strong><span>automated tests in the suite</span></div>
                        <div class="metric"><strong>PSR</strong><span>standards-first core</span></div>
                        <div class="metric"><strong>LTS</strong><span>long-term architecture goal</span></div>
                    </div>
                </div>
                <div class="timeline">
                    <article class="step">
                        <span class="num">1</span>
                        <div>
                            <h3>Generate</h3>
                            <p>Use the CLI for modules, controllers, models, middleware, migrations, seeders, factories, and developer docs.</p>
                        </div>
                    </article>
                    <article class="step">
                        <span class="num">2</span>
                        <div>
                            <h3>Build</h3>
                            <p>Compose features with routing, container bindings, config, ORM models, sessions, queues, storage, validation, and views.</p>
                        </div>
                    </article>
                    <article class="step">
                        <span class="num">3</span>
                        <div>
                            <h3>Harden</h3>
                            <p>Apply authentication, authorization, CSRF, secure cookies, logging, exception rendering, OpenAPI docs, and deployment checks.</p>
                        </div>
                    </article>
                    <article class="step">
                        <span class="num">4</span>
                        <div>
                            <h3>Deploy</h3>
                            <p>Run behind Apache, Nginx, Docker, or PHP-FPM with clean public front-controller routing and production-ready caches.</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="cta">
            <div class="wrap cta-box">
                <div>
                    <h2>Ready for API, SaaS, CRM, AI, and enterprise builds.</h2>
                    <p>WTD Core is the reusable framework layer for Web Tech Domains products: modular, secure, testable, and prepared for modern frontend integrations.</p>
                </div>
                <a class="btn primary" href="/api/status">View Status</a>
            </div>
        </section>
    </main>
</body>
</html>
