## Summary

Describe what changed and why.

## Type

- [ ] Feature
- [ ] Fix
- [ ] Documentation
- [ ] Refactor
- [ ] Test
- [ ] Repository quality workflow / tooling
- [ ] Security

## Reviewers

Required reviewer team:

```text
@Web-Tech-Domains/approvers
```

Protected target branches:

```text
main
master
develop
release/*
hotfix/*
bugfix/*
```

## Checklist

- [ ] I kept the change focused and reviewable.
- [ ] I added or updated tests where behavior changed.
- [ ] I updated documentation where developer workflow changed.
- [ ] I ran `composer test`.
- [ ] I ran `composer analyse`.
- [ ] I ran `vendor/bin/php-cs-fixer fix --dry-run --diff`.
- [ ] I did not commit secrets, local `.env` files, logs, or generated databases.
- [ ] I requested review from `@Web-Tech-Domains/approvers`.

## Notes

Add deployment, migration, compatibility, or follow-up notes here.
