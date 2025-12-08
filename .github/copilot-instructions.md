# ICAS Codebase Instructions for AI Agents

## Architecture Overview

ICAS is a **Kubernetes-ready, multi-service PHP/Laravel architecture** designed for horizontal scaling with three core components:

1. **application/** - Laravel HTTP application (PSR-4 `App\` namespace) serving Inertia+Vue SPA, APIs, and handling auth
2. **communications/** - Laravel Reverb/soketi WebSocket service (PSR-4 `Comm\` namespace) for real-time events and IoT interactions
3. **packages/domain-common/** - Shared domain library (PSR-4 `Icas\Domain\` namespace) used by both services

**Technology Stack**: Laravel, Jetstream/Fortify, Inertia, Vue 3, Vite, Tailwind, PrimeVue, Redis, MySQL, Laravel Reverb (WebSocket)

**Service Communication**: Redis (sessions, cache, pub/sub), MySQL (persistence), HTTP/WebSockets (real-time)

**Frontend Documentation**: See [frontend-design.md](./frontend-design.md) for detailed Vue component architecture, routing, and design patterns.

## Development Environment

### Local Docker Setup

**Option 1: With Local MySQL (All-in-one development)**
```bash
cd docker/
docker compose up -d --profile mysql
```

**Option 2: With External MySQL (Production-like, recommended)**
```bash
cd docker/
# Update .env to point to external DB_HOST
docker compose up -d
```

**Service Architecture:**
- For **development**: Redis and app containers run locally; MySQL can be local (Docker) or external
- For **production (Kubernetes)**: All services (app, communications, Redis, MySQL) run independently on separate nodes/clusters

### Service Mapping

**Local Docker Containers:**
- `icas-app`: PHP-FPM 8.2 on port 9000, code at `/var/www/html` with Redis extension, serves Laravel HTTP + Inertia SPA
- `icas-comm`: PHP-CLI 8.2 on port 6001, code at `/var/www/comm`, runs built-in PHP server (placeholder for Reverb WebSocket)
- `icas-nginx`: HTTPS reverse proxy on ports 80 (redirect) and 443 (TLS), routes to app and comm services
- `redis`: Session/cache store and pub/sub bus (always runs in Docker on port 6379)
- `mysql`: MySQL 8.0 (default credentials: root/root)

**External Database (Development Alternative):**
Set `DB_HOST` in `docker/.env` to point to an external MySQL server (e.g., host machine, separate VM, or cloud database)

The nginx config (`docker/nginx/app.conf`) routes:
- `/` → Laravel application (tries files, falls back to `/index.php`)
- `/socket` → Proxies WebSocket upgrades to communications service on port 6001
- Static assets (css, js, images) → 7-day cache with Cache-Control headers
- HTTP → HTTPS redirect (port 80 to 443)
- TLS 1.2+ with modern ciphers

### Environment Configuration

For **Docker-based setup**, create `docker/.env` with:
```
DB_HOST=mysql              # or external IP/hostname
DB_PORT=3306
DB_DATABASE=icas
DB_USERNAME=root
DB_PASSWORD=root
REDIS_HOST=redis
APP_ENV=local
APP_DEBUG=true
```

Laravel services automatically pick up database credentials from `DB_HOST`, `DB_PORT`, etc.

**Laravel Application (.env)**: 
```
APP_NAME=ICAS
APP_ENV=local
APP_DEBUG=true
APP_URL=https://icas.local
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=icas
DB_USERNAME=root
DB_PASSWORD=root
SESSION_DRIVER=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
BROADCAST_DRIVER=pusher
REDIS_HOST=redis
REDIS_PORT=6379
PUSHER_HOST=icas-comm
PUSHER_PORT=6001
PUSHER_SCHEME=https
```

**Communications Service (.env)**:
```
APP_ENV=local
REDIS_HOST=redis
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=icas
DB_USERNAME=root
DB_PASSWORD=root
```

Both services inherit DB credentials from Docker environment if provided.

### SSL/TLS Certificates

Self-signed certificates are generated in `docker/certs/` and support:
- `localhost`
- `icas.local`
- `www.icas.local`
- `127.0.0.1`

To access via domain names, add to `/etc/hosts` on your machine:
```
127.0.0.1 localhost icas.local www.icas.local
```

Certificates are regenerated via `docker/generate-certs.sh` and valid for 365 days.

## Workspace Structure

VS Code workspace (`icas.code-workspace`) loads three folders:
- `application` - Main app project root
- `communications` - Communications service
- `packages/domain-common` - Shared library

This allows independent work on each component with unified Composer autoloading.

## Key Patterns & Conventions

### PHP Project Setup
- **Autoloading**: PSR-4 namespaces. Add new classes to `app/` or `src/` with matching namespace hierarchy
- **Dependencies**: Managed via `composer.json` in each service. No `composer.lock` tracked (implied from `.gitignore`)
- **Version Requirements**: PHP ^8.1 (8.2 runtime preferred, as seen in Dockerfiles)

### Service Communication
- **Synchronous**: Direct HTTP calls through nginx reverse proxy
- **Asynchronous**: Redis for queuing and session sharing
- **Real-time**: WebSocket connections proxied through `/socket` endpoint to Reverb/soketi service (must use TLS in production)
- **Event Bus**: Redis pub/sub for event exchange between Laravel HTTP app and Communications layer

### Frontend Architecture
- **SPA Framework**: Inertia.js v1.0 + Vue 3 (Composition API) + Pinia state management
- **Styling**: Tailwind CSS 3.x + PrimeVue 4.x components
- **Build Tool**: Vite 7.x with HMR (Hot Module Replacement) support
- **Build Output**: Assets compiled to `public/build/` directory with cache-busting hashes
- **Authentication**: Laravel Jetstream v5.4 + Fortify with secure session cookies backed by Redis
- **Socket Authentication**: Short-lived tokens issued by Laravel app; validated by communications service before accepting connections
- **Key Pages**: Dashboard with sidebar navigation (menu items: Dashboard, Map, Alerts, Sensors)
- **Dashboard Components**: Statistics cards, quick action buttons, map placeholder, alerts list, sensors sidebar

### IoT & External System Integration (Communications Layer Responsibility)
The communications service is responsible for:
- Identifying and authorizing IoT devices, video servers, and phone systems
- **Legacy devices**: HTTP communication (insecure), timed status polls for updates
- **New devices**: Must use JWT tokens, API keys, or client certificate authentication
- **Security**: All communications screened for vulnerabilities; TCP delivery guaranteed
- **State Management**: Maintains connection state and device status across WebSocket network

### Statelessness & Scalability
- **Asset Management**: Build Vite assets in CI; serve via CDN/ingress (not from pods)
- **File Uploads**: Store in S3 with `FILESYSTEM_DISK=s3`; pods must not persist local state
- **Sessions**: Redis-backed via `SESSION_DRIVER=redis` (shared across app replicas)
- **Queues**: Redis-backed via `QUEUE_CONNECTION=redis`; workers run as separate scalable pods

### Production Deployment (Kubernetes)
The architecture supports independent horizontal scaling:
- **Application Deployment**: Stateless Laravel pods (HPA enabled)
- **Communications Deployment**: Reverb/soketi pods for WebSocket handling (HPA enabled, connection pooling)
- **Redis Cluster**: Managed Redis or operator-backed cluster (not single pod)
- **MySQL Cluster**: Primary/replica with automated failover (StatefulSet or managed DB)
- **TLS**: All traffic encrypted; WebSocket connections use `wss://` protocol
- **Ingress**: Routes `/` to app service, `/socket` to communications service with WebSocket upgrade headers

## Critical Developer Workflows

### Starting Development
1. Ensure Docker daemon is running
2. Add domain mappings to `/etc/hosts`: `127.0.0.1 localhost icas.local www.icas.local`
3. Navigate to `docker/` and run: `docker compose -f docker-compose.dev.yml up -d`
4. Wait for all services to start: `docker compose -f docker-compose.dev.yml ps` (app, comms, nginx, redis, mysql)
5. Access application at: `https://icas.local` (accept self-signed certificate warning)
6. Open workspace in VS Code (loads all three projects)
7. Frontend assets auto-compile on code changes (Vite HMR in development)

**First Time Setup:**
- Database migrations run automatically during container startup
- User authentication via Jetstream/Fortify (register or login)
- Session stored in Redis (shared across app replicas)
- Admin seed: `admin@icas.local` / `inmate.2025` (role: `administrator`; default role for new users is `reader`)

**Database Access (MySQL Workbench):**
- Host: `localhost`
- Port: `3306`
- Username: `root`
- Password: `root`
- Database: `icas`

### Adding Shared Domain Logic
1. Add classes to `packages/domain-common/src/` (namespace `Icas\Domain\...`)
2. Both services auto-discover via Composer PSR-4 mapping
3. No need to separately register dependencies if using monorepo patterns

### Debugging
- **PHP-FPM logs**: `docker logs docker-icas-app-1` or `docker compose -f docker-compose.dev.yml logs icas-app`
- **Communications logs**: `docker compose -f docker-compose.dev.yml logs icas-comm`
- **Nginx logs**: `docker compose -f docker-compose.dev.yml logs icas-nginx`
- **Laravel error log**: `docker exec docker-icas-app-1 tail -50 /var/www/html/storage/logs/laravel.log`
- **Redis inspection**: `docker exec docker-redis-1 redis-cli ping` or interactive mode
- **Database query**: `docker exec docker-mysql-1 mysql -uroot -proot icas -e \"SHOW TABLES;\"`
- **NPM build**: `docker exec docker-icas-app-1 npm run build` (or `dev` for watch mode)

## Important Gotchas & Notes

- **Backup files present**: Ignore `.bak.20251117T190343` files (these are historical snapshots)
- **Redis Extension**: Application container includes PHP Redis extension for session/cache support
- **Socket proxy path**: WebSocket client must connect to `/socket` endpoint through nginx (not direct port 6001)
- **Docker-compose volumes**: Application code mounted with `:cached` for performance; use `docker compose exec` for file operations
- **Service dependencies**: nginx waits for icas-comm healthcheck before starting (netcat ping on port 6001)
- **NPM builds**: Run `npm run build` inside container or host depending on Node.js setup; Vite output goes to `public/build/`
- **Legacy peer dependencies**: Use `npm install --legacy-peer-deps` if dependency conflicts occur
- **HTTPS enforcement**: HTTP requests redirect to HTTPS via nginx; update `.env` APP_URL to match domain
- **Email/Mail**: Configure mail driver (currently not set); defaults to `log` driver in local environment

## Current Implementation Status

### ✅ Completed
- Laravel 12 + Jetstream (Inertia stack) installation
- Frontend build pipeline (Vite 7.x, Vue 3, Tailwind CSS)
- Database schema (Laravel migrations with Jetstream tables)
- User roles column added; admin seeder (`admin@icas.local` / `inmate.2025`, role `administrator`)
- Dashboard UI component with sidebar navigation and layout structure
- Redis integration (session, cache, queue drivers)
- HTTPS/SSL support with self-signed certificates
- Docker Compose orchestration with optional MySQL profile
- Service healthchecks and proper startup ordering
- Nginx reverse proxy with HTTP→HTTPS redirect and static caching

### 🚀 In Progress / TODO
- Implement real-time Reverb WebSocket server for communications service
- Connect map component to actual geolocation/mapping service
- Implement alerts management UI and backend logic
- Implement sensors management UI and backend logic
- Add queue workers for background job processing
- Configure email notifications and mail drivers
- Implement IoT device authentication and data ingestion
- Set up API authentication (Sanctum tokens)
- Add comprehensive testing suite
- Performance optimization and load testing

## References

- **PHP Version**: 8.2-fpm for application, 8.2-cli for communications
- **Node.js**: Required for Vite build pipeline (included in application setup)
- **Key Files**:
  - `docker/docker-compose.dev.yml` - Service orchestration with profiles
  - `docker/nginx/app.conf` - HTTPS routing and WebSocket proxying
  - `docker/generate-certs.sh` - Self-signed certificate generation with SANs
  - `application/Dockerfile` - Laravel app with Redis extension
  - `communications/Dockerfile` - Communications service with Laravel + Reverb
  - `application/.env` - Environment config (MySQL, Redis, Reverb, URLs)
  - `application/resources/js/Pages/Dashboard.vue` - Dashboard component
  - `icas.code-workspace` - VS Code workspace configuration
  - `appDesign/AI-Interactions/architecture.md` - Comprehensive architecture documentation
- **Design Resources**:
  - `appDesign/drawio/` - Architecture and UI mockup diagrams (.drawio format)

## Quick Command Reference

```bash
# Start all services
cd docker && docker compose -f docker-compose.dev.yml up -d

# View running services
docker compose -f docker-compose.dev.yml ps

# View logs
docker compose -f docker-compose.dev.yml logs -f icas-app

# Access application
https://icas.local

# Run Laravel artisan commands
docker exec docker-icas-app-1 php artisan <command>

# Build frontend assets
docker exec docker-icas-app-1 npm run build

# Stop all services
docker compose -f docker-compose.dev.yml down

# Stop and remove volumes
docker compose -f docker-compose.dev.yml down -v
```
