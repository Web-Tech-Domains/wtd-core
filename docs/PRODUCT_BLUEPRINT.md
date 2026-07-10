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
- Routing
- Route parameters
- Route groups
- Named routes
- URL generator
- Route cache
- Controllers
- Redirect responses
- File downloads
- Streaming responses
- Cookies
- File-backed sessions
- Configurable middleware
- Middleware pipeline
- Validation
- Database foundation

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
