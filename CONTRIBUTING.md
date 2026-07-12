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
bugfix/forum-asset-paths
hotfix/release-workflow-checks
release/0.1.0-alpha
docs/local-server-guide
```

Repository administrators should protect these branch targets:

```text
main
master
develop
release/*
hotfix/*
bugfix/*
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

You can enable the repository commit template:

```bash
git config commit.template .gitmessage
```

## Pull Requests

Every pull request should:

- Explain the problem and the solution.
- Include tests for behavior changes.
- Keep unrelated refactors out of the PR.
- Update docs when developer workflow changes.
- Pass CI.
- Be approved by a member of `@Web-Tech-Domains/approvers`.

The repository uses `.github/CODEOWNERS` to request reviews from the approvers team for every path. The `Approver Review` workflow checks that at least one active member of `@Web-Tech-Domains/approvers` approved the pull request.

Maintainers should enable branch protection with:

- Require review from Code Owners.
- Require at least 1 approval.
- Require the status check `WTD Core Quality Gate / Approver Review (pull_request_review)`.
- Require status checks on `main`, `master`, `develop`, `release/*`, `hotfix/*`, and `bugfix/*`.

If the approvers team is private, configure the `WTD_APPROVER_TOKEN` secret so the workflow can read team membership.

Do not merge with failing CI, unresolved conversations, or missing Code Owner approval.

## Security

Do not open public issues for vulnerabilities. Follow [SECURITY.md](SECURITY.md).
