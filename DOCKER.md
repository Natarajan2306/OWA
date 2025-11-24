# Docker Setup for Open Web Analytics

This guide explains how to run Open Web Analytics using Docker.

## Prerequisites

- Docker
- Docker Compose

## Quick Start

1. **Create configuration file** (if not exists):
   ```bash
   cp owa-config-dist.php owa-config.php
   ```

2. **Update database configuration** in `owa-config.php`:
   ```php
   define('OWA_DB_TYPE', 'mysql');
   define('OWA_DB_NAME', 'owa');
   define('OWA_DB_HOST', 'db');
   define('OWA_DB_USER', 'owa_user');
   define('OWA_DB_PORT', '3306');
   define('OWA_DB_PASSWORD', 'owa_password');
   ```

3. **Build and start containers**:
   ```bash
   docker-compose up -d
   ```

4. **Access the application**:
   - Open your browser and navigate to `http://localhost:8081`
   - Follow the installation wizard

## Configuration

### Environment Variables

You can customize the database connection by modifying the environment variables in `docker-compose.yml`:

- `OWA_DB_HOST`: Database host (default: `db`)
- `OWA_DB_NAME`: Database name (default: `owa`)
- `OWA_DB_USER`: Database user (default: `owa_user`)
- `OWA_DB_PASSWORD`: Database password (default: `owa_password`)
- `OWA_DB_PORT`: Database port (default: `3306`)

### Volumes

- `./owa-config.php`: Application configuration file
- `./owa-data`: Application data directory (logs, caches)

### Ports

- `8081`: Web server port (mapped to container port 80)
- `3307`: MySQL port (mapped to container port 3306, if you need direct database access)

## Building the Image

To build the Docker image manually:

```bash
docker build -t open-web-analytics .
```

## Running Individual Containers

### Web Container

```bash
docker run -d \
  --name owa-web \
  -p 8081:80 \
  -v $(pwd)/owa-config.php:/var/www/html/owa-config.php \
  -v $(pwd)/owa-data:/var/www/html/owa-data \
  open-web-analytics
```

### Database Container

```bash
docker run -d \
  --name owa-db \
  -e MYSQL_DATABASE=owa \
  -e MYSQL_USER=owa_user \
  -e MYSQL_PASSWORD=owa_password \
  -e MYSQL_ROOT_PASSWORD=root_password \
  -v owa-db-data:/var/lib/mysql \
  mysql:8.0
```

## Troubleshooting

### View Logs

```bash
# Web container logs
docker-compose logs web

# Database container logs
docker-compose logs db

# All logs
docker-compose logs
```

### Access Container Shell

```bash
# Web container
docker-compose exec web bash

# Database container
docker-compose exec db bash
```

### Reset Database

```bash
# Stop containers
docker-compose down

# Remove database volume
docker volume rm open-web-analytics_owa-db-data

# Start containers again
docker-compose up -d
```

### Rebuild After Code Changes

```bash
docker-compose up -d --build
```

## Production Considerations

For production deployments, consider:

1. **Security**:
   - Change default passwords
   - Use environment variables for sensitive data
   - Enable HTTPS (add reverse proxy like nginx)
   - Restrict database access

2. **Performance**:
   - Use a production-ready PHP configuration
   - Enable OPcache
   - Configure proper caching

3. **Backups**:
   - Regularly backup the database volume
   - Backup `owa-data` directory

4. **Monitoring**:
   - Set up health checks
   - Monitor container resources
   - Set up log aggregation

