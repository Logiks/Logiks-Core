# Logiks Framework - Docker Development Environment

This Docker environment provides a complete testing setup for Logiks Framework with PHP 8.0-8.4 and MySQL 8.4, **without requiring any PHP installation on your local machine**.

## Quick Start

### 1. Prerequisites

Only **Docker** and **Docker Compose** are required. No PHP, MySQL, or other dependencies needed on your machine!

- Docker Desktop (Mac/Windows) or Docker Engine (Linux)
- Docker Compose (usually included with Docker Desktop)

Check if you have them:
```bash
docker --version
docker-compose --version
```

### 2. Setup Environment

Run the automated setup script:

```bash
./docker/scripts/setup.sh
```

This will:
- Create necessary directories
- Build all Docker containers (PHP 8.0-8.4)
- Start MySQL 8.4, Redis, Memcached
- Import database schema
- Display access information

### 3. Access the Application

- **Main Application**: http://localhost:8084 (PHP 8.4)
- **phpMyAdmin**: http://localhost:8080
- **MySQL**: localhost:3384 (from host machine)

## Available Containers

### PHP Environments
- `logiks-php80` - PHP 8.0 with Apache
- `logiks-php81` - PHP 8.1 with Apache
- `logiks-php82` - PHP 8.2 with Apache
- `logiks-php83` - PHP 8.3 with Apache
- `logiks-php84` - PHP 8.4 with Apache (exposed on port 8084)

### Database
- `logiks-mysql84` - MySQL 8.4 (port 3384)
- `logiks-mysql57` - MySQL 5.7 for compatibility testing (port 3357)

### Caching
- `logiks-redis` - Redis 7
- `logiks-memcached` - Memcached 1.6

### Management
- `logiks-phpmyadmin` - Database management UI (port 8080)

## Useful Commands

### Container Management

```bash
# Start all containers
docker-compose up -d

# Stop all containers
docker-compose down

# Restart containers
docker-compose restart

# View container status
docker-compose ps

# View logs
./docker/scripts/logs.sh php84
./docker/scripts/logs.sh all -f  # Follow all logs
```

### Testing

```bash
# Quick compatibility test
./docker/scripts/test-compatibility.sh

# Full test suite
./docker/scripts/test-all.sh

# MySQL compatibility test
./docker/scripts/mysql-test.sh
```

### Accessing Containers

```bash
# Access PHP 8.4 shell
./docker/scripts/shell.sh php84

# Access MySQL shell
./docker/scripts/shell.sh mysql84

# Or use docker directly
docker exec -it logiks-php84 bash
docker exec -it logiks-mysql84 mysql -ulogiks -plogiks123
```

### Running PHP Commands

All PHP commands run inside containers:

```bash
# Check PHP version
docker exec logiks-php84 php -v

# Run PHP script
docker exec logiks-php84 php /var/www/html/tests/test.php

# Check loaded extensions
docker exec logiks-php84 php -m

# Check PHP configuration
docker exec logiks-php84 php -i
```

### Database Operations

```bash
# Import SQL file
docker exec -i logiks-mysql84 mysql -ulogiks -plogiks123 logiks_test < database.sql

# Export database
docker exec logiks-mysql84 mysqldump -ulogiks -plogiks123 logiks_test > backup.sql

# Access MySQL shell
docker exec -it logiks-mysql84 mysql -ulogiks -plogiks123 logiks_test
```

## Configuration

### Database Credentials

**From Host Machine:**
- Host: `localhost:3384`
- Database: `logiks_test`
- Username: `logiks`
- Password: `logiks123`
- Root Password: `rootpass123`

**From Containers:**
- Host: `mysql84`
- Database: `logiks_test`
- Username: `logiks`
- Password: `logiks123`

### PHP Configuration

PHP settings are in `docker/php/phpXX.ini` files. After modifying, restart containers:

```bash
docker-compose restart php84
```

### MySQL Configuration

MySQL settings are in `docker/mysql/my.cnf`. After modifying, restart:

```bash
docker-compose restart mysql84
```

## Testing Different PHP Versions

Each PHP version has its own container. Test your code across versions:

```bash
# Test on PHP 8.0
docker exec logiks-php80 php /var/www/html/your-script.php

# Test on PHP 8.4
docker exec logiks-php84 php /var/www/html/your-script.php
```

## Troubleshooting

### Containers won't start

```bash
# Check logs
docker-compose logs

# Remove and rebuild
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d
```

### Port already in use

Edit `docker-compose.yml` to change port mappings:
```yaml
ports:
  - "8084:80"  # Change 8084 to another port
```

### MySQL connection refused

```bash
# Check if MySQL is ready
docker exec logiks-mysql84 mysqladmin ping -h localhost -ulogiks -plogiks123

# Wait a bit longer (MySQL takes ~15 seconds to start)
sleep 15
```

### Permission issues

```bash
# Fix permissions
chmod -R 777 tmp/
chmod +x docker/scripts/*.sh
```

## Development Workflow

### 1. Make Code Changes

Edit files on your local machine. Changes are immediately reflected in containers (via volume mounts).

### 2. Test Changes

```bash
# Test in browser
open http://localhost:8084

# Or run tests
./docker/scripts/test-all.sh
```

### 3. Check Logs

```bash
# Application logs
./docker/scripts/logs.sh php84 -f

# PHP errors
docker exec logiks-php84 tail -f /var/www/html/tmp/logs/php_errors.log
```

## Cleanup

### Remove all containers and volumes

```bash
docker-compose down -v
```

### Remove only containers (keep data)

```bash
docker-compose down
```

### Clean up Docker system

```bash
docker system prune -a --volumes
```

## Next Steps

1. Run compatibility tests: `./docker/scripts/test-compatibility.sh`
2. Open http://localhost:8084 to test the application
3. Use phpMyAdmin at http://localhost:8080 to manage database
4. Review test results in `test-results/` directory

## Support

For issues or questions:
- Check container logs: `docker-compose logs`
- Verify containers are running: `docker-compose ps`
- Review test results: `cat test-results/*.log`
