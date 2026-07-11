# Operations

Operational features are available through configuration files, service providers, and the `core` CLI.

## Configuration

Configuration lives in `config/*.php` and is read through the application configuration repository.

```bash
php core config:cache
php core config:clear
php core optimize
php core optimize:clear
```

## Health And Diagnostics

```bash
php core health
php core diagnostics
php core about
php core env
```

Diagnostics include application name, version, environment, debug flag, base path, boot status, maintenance state, memory usage, and elapsed boot time.

## Maintenance Mode

```bash
php core down
php core up
```

Maintenance mode is persisted under `storage/framework/down`.

## Queue Workers

```bash
php core queue:work
```

Queue support includes in-memory, database, Redis, RabbitMQ, and SQS drivers, failed jobs, retries, batches, and priority queue handling.

## Scheduler

```bash
php core schedule:run
```

Scheduler support includes cron expressions, timezones, overlap protection, background task flags, and maintenance-mode filtering.

## Cache And Storage

```bash
php core cache:clear
```

Cache support includes file, Redis, and Memcached stores, tags, atomic locks, and cache events.

Storage support includes local, S3, Cloudflare R2, Azure, Google Cloud, FTP, and SFTP disks plus signed URLs.

## Deployment Checks

```bash
php core deploy
```

The deployment helper reports readiness checks as JSON so CI or release automation can consume it.

## Web Server Routing

Nginx routing is documented in `docker/nginx/default.conf`. Apache deployments can use `docker/apache/vhost.conf` with `FallbackResource` or the front-controller rewrite rules in `public/.htaccess`.
