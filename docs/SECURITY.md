# Security

Security features are split across `system/Auth`, `system/Authorization`, `system/Security`, `system/Hashing`, and session-related modules.

## Authentication

Implemented authentication components include:

- Session guard
- API token guard and token store
- JWT service
- OAuth state protection
- Password reset tokens
- Email verification tokens
- Remember-me tokens
- Magic link tokens
- MFA TOTP service
- Device registry

## Authorization

Authorization components include:

- Gates
- Policies
- RBAC roles and permissions
- ACL checks
- Role cache

## Application Security

Security utilities include CSRF protection, XSS escaping, SQL identifier validation, encryption, password hashing, rate limiting, CORS handling, trusted proxy client IP resolution, signed URLs, security headers, audit logging, and secrets management.

Default HTTP middleware applies security headers including frame protection, MIME sniffing protection, HSTS, Permissions Policy, Cross-Origin-Opener-Policy, and a restrictive Content Security Policy.

## Operational Guidance

- Keep `app.debug` disabled in production.
- Use HTTPS when issuing session, remember-me, magic link, password reset, email verification, and API tokens.
- Rotate encryption keys and secrets through deployment automation.
- Use rate limiting on authentication and public write endpoints.
- Cache authorization roles only with a clear invalidation path.
