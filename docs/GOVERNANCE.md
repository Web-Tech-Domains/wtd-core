# Governance

WTD Core is maintained by Web Tech Domains.

## Roles

- Maintainers manage releases, repository settings, branch protection, and security responses.
- Approvers review pull requests and approve changes through the `@Web-Tech-Domains/approvers` team.
- Contributors propose issues, documentation, tests, fixes, and features through pull requests.

## Decision Making

Technical decisions should prioritize:

- Security and stable defaults.
- Long-term maintainability.
- PSR compatibility and clean architecture.
- Small, reviewable changes.
- Clear developer experience.

For major changes, open an issue first and document:

- Problem statement.
- Proposed API or behavior.
- Compatibility impact.
- Security impact.
- Migration path.

## Pull Request Approval

Every pull request requires approval from a member of:

```text
@Web-Tech-Domains/approvers
```

This is declared in `.github/CODEOWNERS`. Repository administrators must enable branch protection with Code Owner review for enforcement on:

```text
main
master
develop
release/*
hotfix/*
bugfix/*
```

## Maintainer Responsibilities

Maintainers should:

- Keep repository quality workflow checks required and passing.
- Review security reports privately.
- Keep dependencies updated.
- Avoid merging unrelated changes together.
- Keep release notes accurate.
