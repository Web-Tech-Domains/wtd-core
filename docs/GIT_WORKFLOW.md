# Git Workflow

WTD Core uses a standard pull-request workflow.

## Required Reviews

Every pull request must be approved by a member of:

```text
@Web-Tech-Domains/approvers
```

The repository declares this in `.github/CODEOWNERS`:

```text
* @Web-Tech-Domains/approvers
```

## GitHub Branch Protection

Repository administrators should protect the default branch and enable:

- Require a pull request before merging.
- Require approvals.
- Require review from Code Owners.
- Require status checks to pass.
- Require branches to be up to date before merging.
- Restrict force pushes.
- Restrict deletions.

## Local Branches

Use focused branch names:

```text
feature/module-generator-views
fix/error-renderer-layout
docs/git-workflow
```

## Commits

Use Conventional Commits:

```text
feat: add module auto-discovery
fix: handle missing env as development
docs: document code owner review
```
