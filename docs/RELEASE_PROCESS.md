# Release Process

WTD Core follows Semantic Versioning.

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
git tag -a v0.1.0-alpha -m "Release v0.1.0-alpha"
git push origin v0.1.0-alpha
```

## Branch Protection

The default branch should require:

- Pull requests before merging.
- Code Owner review.
- Passing CI.
- Up-to-date branches.
- No force pushes.
- No branch deletions.
