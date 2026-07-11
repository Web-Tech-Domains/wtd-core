# Contributing to WTD Core

Thank you for helping improve WTD Core. This project follows a practical open-source workflow focused on small, reviewable changes and strong automated checks.

## Requirements

- PHP 8.3+
- Composer
- Node.js and npm for frontend asset work
- Git

## Local Setup

```bash
composer install
npm install
copy .env.example .env
php core serve
```

On macOS or Linux, use:

```bash
cp .env.example .env
```

## Quality Checks

Run these before opening a pull request:

```bash
composer validate --strict
composer test
composer analyse
vendor/bin/php-cs-fixer fix --dry-run --diff
npm run build
```

Use `composer cs:fix` to apply PHP style fixes.

## Branches

Use short, descriptive branch names:

```text
feature/module-discovery
fix/error-page-rendering
docs/local-server-guide
```

## Commits

Use Conventional Commits:

```text
feat: add module route auto-discovery
fix: render branded error pages
docs: document local server command
test: cover env fallback behavior
```

Common types: `feat`, `fix`, `docs`, `test`, `refactor`, `chore`, `ci`, `perf`, `security`.

## Pull Requests

Every pull request should:

- Explain the problem and the solution.
- Include tests for behavior changes.
- Keep unrelated refactors out of the PR.
- Update docs when developer workflow changes.
- Pass CI.

## Security

Do not open public issues for vulnerabilities. Follow [SECURITY.md](SECURITY.md).
