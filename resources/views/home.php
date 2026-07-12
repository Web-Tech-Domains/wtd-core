<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ description }}">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="manifest" href="/site.webmanifest">
    <title>{{ name }} | Web Tech Domains</title>
    {!! assetTags !!}
</head>
<body data-wtd-app>
    <header class="topbar">
        <div class="wrap nav">
            <a class="brand" href="/">
                <img class="brand-logo" src="/favicon.svg" alt="WTD Core">
                <span>Web Tech Domains / {{ name }}</span>
            </a>
            <nav class="navlinks" aria-label="Primary navigation">
                <a href="/" class="active">Home</a>
                <a href="/forums">Forums</a>
                <a href="/health">Health</a>
                <a href="/api/status">API Status</a>
                <a href="/docs/api">OpenAPI</a>
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
                        <a class="btn primary" href="/forums">Open Forums</a>
                        <a class="btn" href="/docs/api">View API Docs</a>
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

        <section class="section" id="features">
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

        <section class="section forums-spotlight">
            <div class="wrap forums-spotlight-box">
                <div class="forums-spotlight-text">
                    <span class="eyebrow"><span class="dot"></span> Modules spotlight</span>
                    <h2>Active Community Forums</h2>
                    <p>WTD Core comes pre-packaged with a fully-functional Forums module. Engage with fellow core developers, discuss package hooks, and share SaaS best practices in a persistent database-backed space.</p>
                    <a class="btn primary" href="/forums">Visit Community Forums</a>
                </div>
                <div class="forums-spotlight-preview">
                    <div class="forums-preview-card">
                        <div class="forums-preview-main">
                            <span class="forums-preview-badge violet">Announcements</span>
                            <h4>Proposal: changelog module release notes workflow</h4>
                        </div>
                        <span class="forums-preview-meta">24 replies</span>
                    </div>
                    <div class="forums-preview-card">
                        <div class="forums-preview-main">
                            <span class="forums-preview-badge green">Framework Help</span>
                            <h4>Best practices for model events and moderation logs</h4>
                        </div>
                        <span class="forums-preview-meta">9 replies</span>
                    </div>
                    <div class="forums-preview-card">
                        <div class="forums-preview-main">
                            <span class="forums-preview-badge blue">Packages</span>
                            <h4>How should forum modules expose package hooks?</h4>
                        </div>
                        <span class="forums-preview-meta">18 replies</span>
                    </div>
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
                        <div class="metric"><strong>251+</strong><span>automated tests in the suite</span></div>
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
