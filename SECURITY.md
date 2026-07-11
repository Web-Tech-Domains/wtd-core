# Security Policy

WTD Core is intended for API, SaaS, enterprise, and cloud-native PHP applications. Security reports are handled privately first.

## Supported Versions

| Version | Supported |
| --- | --- |
| 0.1.x | Yes |
| < 0.1 | No |

## Reporting a Vulnerability

Do not create a public GitHub issue for security vulnerabilities.

Send reports to:

```text
info@webtechdomains.in
```

Include:

- Affected version or commit.
- Clear reproduction steps.
- Impact and attack scenario.
- Any proof-of-concept code, logs, or screenshots.
- Suggested mitigation, if known.

## Response Process

Maintainers will:

- Acknowledge valid reports as soon as practical.
- Investigate and confirm impact.
- Prepare a fix and tests.
- Publish release notes or advisories when appropriate.

## Security Expectations

Contributors should avoid committing:

- Secrets, API keys, or real credentials.
- Production `.env` files.
- Private customer data.
- Generated local databases or logs.

Use `.env.example` for safe placeholders.
