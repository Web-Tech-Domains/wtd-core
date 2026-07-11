# CLI Reference

The `core` executable is the framework command-line entry point.

```bash
php core list
php core help <command>
```

## Commands

| Command | Purpose |
| --- | --- |
| `about` | Print framework name and version. |
| `api:docs` | Generate OpenAPI documentation. |
| `app:new` | Create a minimal WTD Core project skeleton. |
| `benchmark` | Benchmark an HTTP path through the framework kernel. |
| `cache:clear` | Clear the application cache store. |
| `config:cache` | Build the configuration cache. |
| `config:clear` | Clear the configuration cache. |
| `db:seed` | Run database seeders. |
| `deploy` | Run deployment readiness checks. |
| `diagnostics` | Print runtime diagnostics as JSON. |
| `down` | Enable maintenance mode. |
| `env` | Print current application environment. |
| `health` | Print application health as JSON. |
| `help` | Show command help. |
| `ide:helper` | Generate IDE helper stubs for framework services. |
| `list` | List available commands. |
| `make:command` | Generate an application console command. |
| `make:controller` | Generate an application controller. |
| `make:model` | Generate an application model. |
| `make:resource` | Generate an API resource controller and route snippet. |
| `migrate` | Run pending database migrations. |
| `migrate:rollback` | Roll back the latest database migration batch. |
| `optimize` | Build framework optimization caches. |
| `optimize:clear` | Clear framework optimization caches. |
| `queue:work` | Process the next queued job. |
| `route:cache` | Build the route cache. |
| `route:clear` | Clear the route cache. |
| `schedule:run` | Run due scheduled tasks. |
| `test` | Print or run the project test command. |
| `up` | Disable maintenance mode. |

## Common Workflows

```bash
php core make:controller HomeController
php core make:model User
php core make:resource Post --model=Post
php core app:new demo
php core api:docs
php core ide:helper
php core benchmark / --iterations=100
php core migrate
php core db:seed
php core queue:work
php core schedule:run
php core cache:clear
php core deploy
```
