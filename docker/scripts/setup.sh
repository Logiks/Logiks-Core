#!/bin/bash

# Quick setup script for Logiks Docker environment
# This script runs entirely in Docker - no local PHP installation needed

set -e  # Exit on error

echo "=================================================="
echo "Logiks Framework - Docker Environment Setup"
echo "=================================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Create necessary directories
echo "Creating required directories..."
mkdir -p tmp/cache
mkdir -p tmp/logs
mkdir -p tmp/sessions
mkdir -p test-results
mkdir -p data/uploads

# Set permissions
echo "Setting permissions..."
chmod -R 777 tmp/
chmod -R 777 data/

echo -e "${GREEN}✓ Directories created${NC}"
echo ""

# Build Docker containers
echo "Building Docker containers (this may take a few minutes)..."
docker-compose build --no-cache

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Containers built successfully${NC}"
else
    echo -e "${RED}✗ Container build failed${NC}"
    exit 1
fi
echo ""

# Start containers
echo "Starting Docker containers..."
docker-compose up -d

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Containers started successfully${NC}"
else
    echo -e "${RED}✗ Failed to start containers${NC}"
    exit 1
fi
echo ""

# Wait for MySQL to be ready
echo "Waiting for MySQL 8.4 to be ready..."
sleep 15

# Check if MySQL is accessible
docker exec logiks-mysql84 mysqladmin ping -h localhost -ulogiks -plogiks123 > /dev/null 2>&1

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ MySQL 8.4 is ready${NC}"
else
    echo -e "${YELLOW}⚠ MySQL may still be initializing. Waiting another 10 seconds...${NC}"
    sleep 10
fi
echo ""

# Import database schema
echo "Importing database schema..."
if [ -f ".install/sql/MySQL/schema-coredb.sql" ]; then
    docker exec -i logiks-mysql84 mysql -ulogiks -plogiks123 logiks_test < .install/sql/MySQL/schema-coredb.sql
    echo -e "${GREEN}✓ Schema imported${NC}"
else
    echo -e "${YELLOW}⚠ Schema file not found, skipping...${NC}"
fi

if [ -f ".install/sql/MySQL/data-coredb.sql" ]; then
    docker exec -i logiks-mysql84 mysql -ulogiks -plogiks123 logiks_test < .install/sql/MySQL/data-coredb.sql
    echo -e "${GREEN}✓ Data imported${NC}"
else
    echo -e "${YELLOW}⚠ Data file not found, skipping...${NC}"
fi
echo ""

# Show container status
echo "Container Status:"
docker-compose ps
echo ""

# Show access information
echo "=================================================="
echo -e "${GREEN}Setup Complete!${NC}"
echo "=================================================="
echo ""
echo "Access Points:"
echo "  • PHP 8.0:      Container 'logiks-php80'"
echo "  • PHP 8.1:      Container 'logiks-php81'"
echo "  • PHP 8.2:      Container 'logiks-php82'"
echo "  • PHP 8.3:      Container 'logiks-php83'"
echo "  • PHP 8.4:      http://localhost:8084"
echo "  • phpMyAdmin:   http://localhost:8080"
echo "  • MySQL 8.4:    localhost:3384 (user: logiks, pass: logiks123)"
echo "  • Redis:        localhost:6379"
echo "  • Memcached:    localhost:11211"
echo ""
echo "Database Credentials:"
echo "  • Host:         mysql84 (from containers) or localhost:3384 (from host)"
echo "  • Database:     logiks_test"
echo "  • Username:     logiks"
echo "  • Password:     logiks123"
echo "  • Root Pass:    rootpass123"
echo ""
echo "Useful Commands:"
echo "  • View logs:              docker-compose logs -f"
echo "  • Stop containers:        docker-compose down"
echo "  • Restart:                docker-compose restart"
echo "  • Run tests:              ./docker/scripts/test-all.sh"
echo "  • Access PHP 8.4 shell:   docker exec -it logiks-php84 bash"
echo "  • Access MySQL shell:     docker exec -it logiks-mysql84 mysql -ulogiks -plogiks123"
echo ""
echo "Next Steps:"
echo "  1. Open http://localhost:8084 in your browser"
echo "  2. Run compatibility tests: ./docker/scripts/test-compatibility.sh"
echo "  3. Run full test suite: ./docker/scripts/test-all.sh"
echo ""
