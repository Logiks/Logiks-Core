#!/bin/bash

# Easy access to container shells

if [ -z "$1" ]; then
    echo "Usage: ./docker/scripts/shell.sh [container]"
    echo ""
    echo "Available containers:"
    echo "  php80      - PHP 8.0 environment"
    echo "  php81      - PHP 8.1 environment"
    echo "  php82      - PHP 8.2 environment"
    echo "  php83      - PHP 8.3 environment"
    echo "  php84      - PHP 8.4 environment"
    echo "  mysql84    - MySQL 8.4 database"
    echo "  mysql57    - MySQL 5.7 database"
    echo ""
    echo "Example: ./docker/scripts/shell.sh php84"
    exit 1
fi

CONTAINER="logiks-$1"

# Check if container exists and is running
if ! docker ps | grep -q "$CONTAINER"; then
    echo "Error: Container '$CONTAINER' is not running"
    echo "Run: docker-compose up -d $1"
    exit 1
fi

# Access the container
case "$1" in
    mysql84|mysql57)
        echo "Accessing MySQL shell..."
        docker exec -it $CONTAINER mysql -ulogiks -plogiks123 logiks_test
        ;;
    *)
        echo "Accessing bash shell..."
        docker exec -it $CONTAINER bash
        ;;
esac
