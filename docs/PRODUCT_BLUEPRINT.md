# WTD Core Product Blueprint

## Directory Structure
app/
bootstrap/
config/
database/
docs/
packages/
public/
routes/
storage/
system/
tests/

## Core Modules
- Kernel
- Router
- HTTP Request/Response
- Service Container
- Middleware
- Validation
- ORM
- Authentication
- Authorization
- Queue
- Scheduler
- Cache
- Events
- Notifications
- Logging
- Storage
- CLI

## Standards
- PHP 8.3+
- PSR-1,4,7,11,12,14,15,17,18
- Composer
- PHPUnit
- PHPStan
- PHP-CS-Fixer
- SemVer
- Conventional Commits
- SOLID, DRY, KISS, YAGNI
- Clean Architecture
- DDD compatible
- Hexagonal Architecture friendly

## Architecture Specification
- Source: `docs/SOFTWARE_ARCHITECTURE_SPECIFICATION.md`
- Project type: Open source / enterprise PHP framework
- Target use cases: APIs, SaaS applications, enterprise software, and cloud-native deployments
- Architectural intent: Laravel-like developer experience with Slim-like lightness and Symfony-like flexibility

## Roadmap
### 0.1 Alpha
- Application kernel and lifecycle
- Bootstrap file
- Service container
- Service providers
- Configurable provider bootstrapping
- PSR-11 container contract support
- PSR-3 logger contract support
- Configuration repository, PHP config loader, and config cache
- Environment loader
- Boot-time error handler registration
- File logger
- Filesystem helper
- Health check
- Persistent maintenance mode state
- Performance timer
- Memory monitor
- Console kernel, parsed input, and command registry
- CLI `about`, `env`, `health`, `diagnostics`, `down`, `up`, `list`, `help`, `config:cache`, `config:clear`, `optimize`, and `optimize:clear` commands
- Initial PHPUnit, PHPStan, and PHP-CS-Fixer setup

### 0.5 Beta
- HTTP kernel
- Request and response objects
- HTTP exception rendering
- 404 and 405 HTTP error responses
- Full HTTP method routing
- Automatic OPTIONS responses
- Routing
- Route parameters
- Route groups
- Named routes
- Domain routing
- API versioning
- URL generator
- Route cache
- Controllers
- Redirect responses
- File downloads
- Streaming responses
- Cookies
- File-backed sessions
- Configurable middleware
- Route middleware
- Middleware pipeline

### 0.6 Dependency Injection
- Transient bindings
- Singleton bindings
- Scoped bindings
- Interface bindings
- Factory bindings
- Auto resolution
- Tagged services
- Contextual bindings

### 0.7 Validation
- Request validation helpers
- Form request validation classes
- Controller form request injection
- Nested input validation and validated output with dot notation
- Conditional validation rules
- Custom validation messages
- Custom validation rule extensions
- HTTP 422 JSON validation error responses
- Common scalar, comparison, exact size, regex, confirmation, date, URL, accepted/declined, and membership rules

### 0.8 Database
- PDO connection manager
- Query builder
- Query grammar
- Schema builder
- Table creation and dropping
- Basic column definitions
- Migration contract
- Migration repository and batch tracking
- Migration runner and rollback support
- Pagination
- Chunking
- CLI `migrate` and `migrate:rollback` commands
- Seeder contract
- Seeder runner
- CLI `db:seed` command
- Database factory base
- Query executed database events
- Database service provider bindings

### 1.0 LTS
- ORM model foundation
- ORM query builder and local scopes
- HasOne, HasMany, and BelongsTo ORM relationships
- Many-to-many ORM relationships
- Polymorphic ORM relationships
- ORM lifecycle events and observers
- ORM soft deletes
- ORM UUID primary key support
- ORM casting, accessors, and mutators
- ORM repository abstraction
- Session authentication
- JWT service
- OAuth state protection
- API tokens
- Password reset tokens
- Email verification tokens
- Remember-me tokens
- Magic link tokens
- MFA TOTP service
- Device management
- RBAC authorization
- Role permissions
- Policies
- Gates
- ACL
- Role cache
- CSRF protection
- XSS escaping
- SQL identifier validation and parameterized query coverage
- Encryption
- Hashing
- Rate limiter
- CORS headers
- Trusted proxy client IP resolution
- Signed URLs
- Security headers
- Audit logging
- Secrets manager
- Queue job contract
- Queue workers
- Failed jobs and retry
- Job batches
- Priority queue handling
- Database queue driver
- Redis queue driver
- RabbitMQ queue driver
- AWS SQS queue driver
- Scheduler cron parser
- Scheduler timezone support
- Scheduler overlap protection
- Scheduler background execution flag
- Scheduler maintenance-mode filtering
- CLI `schedule:run` command
- Event dispatcher
- Event listeners
- Event subscribers
- Event broadcasting
- Queued events and listeners
- Event discovery
- Email notifications
- SMS notifications
- WhatsApp notifications
- Telegram notifications
- Slack notifications
- Firebase notifications
- Database notifications
- Webhook notifications
- SMTP mail transport
- SES mail transport
- Mailgun mail transport
- Postmark mail transport
- SendGrid mail transport
- Markdown mail rendering
- Mail templates
- Mail attachments
- Inline mail images
- File cache store
- Redis cache store
- Memcached cache store
- Cache tags
- Atomic cache locks
- Cache events
- Local storage disk
- S3 storage disk
- Cloudflare R2 storage disk
- Azure storage disk
- Google Cloud storage disk
- FTP storage disk
- SFTP storage disk
- Storage signed URLs
- CLI generators (`make:*`)
- CLI project creation
- CLI queue worker command
- CLI cache management
- CLI testing helper
- CLI deployment helper
- Documentation index
- Getting started guide
- Architecture guide
- CLI reference
- HTTP and routing guide
- Database and ORM guide
- Security guide
- Operations guide
- Documentation regression tests
- Developer experience configuration
- Debug toolbar
- Profiler
- API documentation UI
- OpenAPI generator
- API resource code generator
- Benchmark CLI tool
- IDE helper generator
- HTML error pages
- CLI runtime wrapper
- View renderer
- WebSocket handshake, frames, and channels

### 2.0 Enterprise
- Marketplace package manifests
- Local package discovery
- Package installation metadata
- Package config publishing
- Installed provider auto-registration
- Multi-tenancy
- Tenant resolver middleware
- AI package integrations
- AI provider manager
- Enterprise monitoring and administration
- Admin system report
