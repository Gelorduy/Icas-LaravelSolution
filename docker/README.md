# ICAS Docker Development Environment

## Quick Start

### 1. Generate SSL Certificates (First Time Only)

```bash
cd docker/
chmod +x generate-certs.sh
./generate-certs.sh
```

This generates a self-signed certificate valid for 365 days.

### 2. Start Docker Services

**Option A: With Local MySQL (all-in-one)**
```bash
docker compose up -d --profile mysql
```

**Option B: With External MySQL (production-like)**
```bash
# First, create docker/.env and set DB_HOST to your external server
cp .env.example .env
# Edit .env and change DB_HOST to your external MySQL server
docker compose up -d
```

### 3. Access the Application

```
https://localhost
or
https://icas.local  (if you add 127.0.0.1 icas.local to /etc/hosts)
```

**Note**: Your browser will show a certificate warning for the self-signed certificate. Click "Accept" or "Proceed Anyway" to continue.

## Services

| Service | URL | Purpose |
|---------|-----|---------|
| **Application** | https://localhost | Main Laravel HTTP app + Inertia SPA |
| **Communications** | ws://localhost:6001 | WebSocket/Reverb service (proxied via /socket) |
| **Nginx** | port 80/443 | Reverse proxy with SSL |
| **Redis** | port 6379 | Session store, cache, pub/sub |
| **MySQL** | port 3306 | Database (optional, with --profile mysql) |

## SSL/TLS Configuration

- **Protocol**: TLSv1.2 and TLSv1.3
- **Certificate Location**: `docker/certs/icas.crt`
- **Private Key Location**: `docker/certs/icas.key`
- **Validity**: 365 days from generation

### Regenerate Certificates

If certificates expire or you need to regenerate:
```bash
rm docker/certs/icas.*
./docker/generate-certs.sh
```

## Environment Configuration

Create `docker/.env` to customize settings:

```env
# Database
DB_HOST=mysql              # or external IP/hostname
DB_PORT=3306
DB_DATABASE=icas
DB_USERNAME=root
DB_PASSWORD=root

# Redis
REDIS_HOST=redis
REDIS_PORT=6379

# Application
APP_ENV=local
APP_DEBUG=true
```

## Useful Commands

### View Logs
```bash
# All services
docker compose logs -f

# Specific service
docker compose logs -f icas-app
docker compose logs -f icas-nginx
```

### Execute Commands in Container
```bash
# Laravel Artisan
docker exec -it docker-icas-app-1 php artisan migrate
docker exec -it docker-icas-app-1 php artisan tinker

# Composer
docker exec -it docker-icas-app-1 composer install
docker exec -it docker-icas-app-1 composer update

# Redis CLI
docker exec -it docker-redis-1 redis-cli
```

### Stop and Clean
```bash
# Stop services
docker compose down

# Remove volumes (careful - deletes data)
docker compose down -v

# Rebuild containers
docker compose build --no-cache
docker compose up -d --profile mysql
```

## Troubleshooting

### Certificate Not Working
```bash
# Regenerate certificates
./docker/generate-certs.sh

# Restart nginx
docker compose restart icas-nginx
```

### Database Connection Issues
```bash
# Check DB_HOST in docker/.env
cat docker/.env | grep DB_HOST

# For local MySQL, ensure --profile mysql is used
docker compose ps  # should show mysql container

# Test connection
docker exec -it docker-icas-app-1 php artisan tinker
>>> DB::connection()->getPdo()
```

### Node/npm Dependencies Missing
```bash
# Install npm packages in container
docker exec -it docker-icas-app-1 npm install

# Build frontend assets
docker exec -it docker-icas-app-1 npm run build
```

### Port Already in Use
```bash
# Check which process is using port 80 or 443
sudo lsof -i :80
sudo lsof -i :443

# Or use different ports in docker-compose.dev.yml
# Change "80:80" to "8080:80" for example
```

## Architecture

```
Browser (HTTPS)
    ↓
nginx (443) ──┬─→ Laravel App (PHP-FPM:9000)
              │
              └─→ WebSocket Proxy (→ Communications:6001)
                       ↓
                  Reverb/soketi
    
Redis (Session, Cache, Pub/Sub)
MySQL (Data Persistence)
```

## Next Steps

1. Complete Jetstream/Inertia setup: `npm install && npm run build`
2. Create database tables: `php artisan migrate`
3. Access login page and create an account
4. Build dashboard with upper and lateral menus

## References

- [SSL Certificate Generation](generate-certs.sh)
- [Nginx Configuration](nginx/app.conf)
- [Docker Compose File](docker-compose.dev.yml)
