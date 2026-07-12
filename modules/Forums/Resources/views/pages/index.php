<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Open-source forum module for WTD Core applications.">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="stylesheet" href="/assets/modules/forums.css">
    <title>Forums | WTD Core</title>
    {!! assetTags !!}
</head>
<body class="forums-page">
    <div id="forums-app" data-forums-app>
        <header class="forums-topbar">
            <a class="forums-brand" href="/">
                <img src="/favicon.svg" alt="WTD Core">
                <span>WTD Forums</span>
            </a>
            <nav aria-label="Forums navigation">
                <a href="/forums">Forums</a>
                <a href="/docs/api">API Docs</a>
                <a href="/health">Health</a>
            </nav>
        </header>

        <main class="forums-shell">
            <aside class="forums-sidebar">
                <div class="forums-panel">
                    <p class="forums-eyebrow">Open source discussion</p>
                    <h1>Forums</h1>
                    <p>Discuss framework usage, package ideas, release workflows, and implementation questions.</p>
                    <a class="forums-primary" href="#new-topic">New topic</a>
                </div>
                <div class="forums-panel forums-guidelines">
                    <h2>Guidelines</h2>
                    <ul>
                        <li>Search before opening a duplicate topic.</li>
                        <li>Keep titles specific and actionable.</li>
                        <li>Include version, environment, and reproduction steps.</li>
                    </ul>
                </div>
            </aside>

            <section class="forums-content">
                <div class="forums-stats">
                    <article><strong>128</strong><span>Topics</span></article>
                    <article><strong>1,482</strong><span>Replies</span></article>
                    <article><strong>342</strong><span>Members</span></article>
                </div>

                <section class="forums-board" aria-label="Forum topics">
                    <div class="forums-board-head">
                        <div>
                            <p class="forums-eyebrow">Latest activity</p>
                            <h2>Community topics</h2>
                        </div>
                        <label>
                            <span>Filter</span>
                            <select aria-label="Filter forum topics">
                                <option>All categories</option>
                                <option>Announcements</option>
                                <option>Framework Help</option>
                                <option>Packages</option>
                                <option>Security</option>
                            </select>
                        </label>
                    </div>

                    <div class="forums-topic-list">
                        <article class="forums-topic">
                            <div>
                                <span class="forums-badge">Packages</span>
                                <h3>How should forum modules expose package hooks?</h3>
                                <p>Core Team opened this for module package maintainers.</p>
                            </div>
                            <dl>
                                <div><dt>Replies</dt><dd>18</dd></div>
                                <div><dt>Views</dt><dd>312</dd></div>
                                <div><dt>Status</dt><dd>Open</dd></div>
                            </dl>
                        </article>
                        <article class="forums-topic">
                            <div>
                                <span class="forums-badge forums-badge-green">Framework Help</span>
                                <h3>Best practices for model events and moderation logs</h3>
                                <p>Maintainer marked this topic as answered.</p>
                            </div>
                            <dl>
                                <div><dt>Replies</dt><dd>9</dd></div>
                                <div><dt>Views</dt><dd>144</dd></div>
                                <div><dt>Status</dt><dd>Answered</dd></div>
                            </dl>
                        </article>
                        <article class="forums-topic">
                            <div>
                                <span class="forums-badge forums-badge-violet">Announcements</span>
                                <h3>Proposal: changelog module release notes workflow</h3>
                                <p>Web Tech Domains pinned this implementation discussion.</p>
                            </div>
                            <dl>
                                <div><dt>Replies</dt><dd>24</dd></div>
                                <div><dt>Views</dt><dd>401</dd></div>
                                <div><dt>Status</dt><dd>Pinned</dd></div>
                            </dl>
                        </article>
                    </div>
                </section>

                <section id="new-topic" class="forums-composer">
                    <div>
                        <p class="forums-eyebrow">Draft topic</p>
                        <h2>Start a useful discussion</h2>
                    </div>
                    <form>
                        <input type="text" placeholder="Topic title" aria-label="Topic title">
                        <textarea placeholder="Describe the question, decision, or proposal." aria-label="Topic body"></textarea>
                        <button type="button">Create draft</button>
                    </form>
                </section>
            </section>
        </main>
    </div>
    <script type="application/json" id="forums-initial-state">{!! forumPayload !!}</script>
</body>
</html>
