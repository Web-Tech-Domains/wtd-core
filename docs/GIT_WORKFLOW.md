# Git Workflow

WTD Core uses a standard pull-request workflow.

## Required Reviews

Every pull request must be approved by a member of:

```text
@Web-Tech-Domains/approvers
```

Approvals from users outside that team do not satisfy the project review policy.

The repository declares this in `.github/CODEOWNERS`:

```text
* @Web-Tech-Domains/approvers
```

## GitHub Branch Protection

Repository administrators should protect these branch names and patterns:

```text
main
master
develop
release/*
hotfix/*
bugfix/*
```

Enable these rules for every protected branch target:

- Require a pull request before merging.
- Require at least 1 approval.
- Require review from Code Owners.
- Require status checks to pass.
- Require the status check `WTD Core Quality Gate / Approver Review`.
- Require branches to be up to date before merging.
- Restrict force pushes.
- Restrict deletions.

The `Approver Review` job in `WTD Core Quality Gate` validates that at least one active member of `@Web-Tech-Domains/approvers` has approved the pull request. The job does not check out or execute pull request code.

If the team is private or GitHub does not allow the default workflow token to read team membership, add a repository or organization secret named:

```text
WTD_APPROVER_TOKEN
```

The token should have permission to read organization team membership.

## Local Branches

Use focused branch names:

```text
feature/module-generator-views
fix/error-renderer-layout
bugfix/forum-asset-paths
hotfix/release-workflow-checks
release/0.1.0-alpha
docs/git-workflow
```

## Commits

Use Conventional Commits:

```text
feat: add module auto-discovery
fix: handle missing env as development
docs: document code owner review
```

To use the repository commit template locally:

```bash
git config commit.template .gitmessage
```
