#!/bin/bash

# View Docker container logs

if [ -z "$1" ]; then
    echo "Usage: ./docker/scripts/logs.sh [container] [options]"
    echo ""
    echo "Available containers:"
    echo "  php80, php81, php82, php83, php84"
    echo "  mysql84, mysql57"
    echo "  phpmyadmin, redis, memcached"
    echo "  all         - Show all logs"
    echo ""
    echo "Options:"
    echo "  -f, --follow    Follow log output"
    echo "  -n NUM          Number of lines to show (default: 100)"
    echo ""
    echo "Examples:"
    echo "  ./docker/scripts/logs.sh php84"
    echo "  ./docker/scripts/logs.sh php84 -f"
    echo "  ./docker/scripts/logs.sh all --follow"
    exit 1
fi

CONTAINER="$1"
shift

if [ "$CONTAINER" = "all" ]; then
    docker-compose logs "$@"
else
    docker-compose logs "logiks-$CONTAINER" "$@"
fi
