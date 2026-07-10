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
- Schema builder
- Table creation and dropping
- Basic column definitions
- Migration contract
- Migration repository and batch tracking
- Migration runner and rollback support
- CLI `migrate` and `migrate:rollback` commands
- Database service provider bindings

### 1.0 LTS
- ORM
- Authentication
- Authorization
- Queue
- Scheduler
- Documentation

### 2.0 Enterprise
- Marketplace
- Multi-tenancy
- AI package integrations
- Enterprise monitoring and administration
