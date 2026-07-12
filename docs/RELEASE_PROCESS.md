# Release Process

WTD Core follows Semantic Versioning.

Current version source of truth:

```text
VERSION
```

Current release:

```text
0.1.0-alpha
```

## Version Format

```text
MAJOR.MINOR.PATCH[-prerelease]
```

Examples:

```text
0.1.0-alpha
0.1.1
1.0.0
```

Use the plain Semantic Version in `VERSION`, package metadata, runtime metadata, and docs. Use the `v` prefix only for Git tags.

```text
VERSION file: 0.1.0-alpha
Git tag:      v0.1.0-alpha
```

The following files must match `VERSION` before release:

- `system/Application/Application.php`
- `package.json`
- `README.md`
- `CHANGELOG.md`

## Release Checklist

Before tagging a release:

- Ensure all pull requests are approved by `@Web-Tech-Domains/approvers`.
- Run `composer validate --strict`.
- Run `composer test`.
- Run `composer analyse`.
- Run `vendor/bin/php-cs-fixer fix --dry-run --diff`.
- Run `npm run build` when frontend assets changed.
- Update `CHANGELOG.md`.
- Verify `README.md` and docs reflect current behavior.
- Confirm no secrets, local databases, logs, or `.env` files are committed.

## Tagging

Use signed or protected tags when possible:

```bash
git checkout -b release/0.1.0-alpha
git tag -a v0.1.0-alpha -m "Release v0.1.0-alpha"
git push origin v0.1.0-alpha
```

## Protected Branches

Protect these branch targets:

```text
main
master
develop
release/*
hotfix/*
bugfix/*
```

Every protected branch should require:

- Pull requests before merging.
- Code Owner review.
- Passing repository quality workflow checks.
- Up-to-date branches.
- No force pushes.
- No branch deletions.
