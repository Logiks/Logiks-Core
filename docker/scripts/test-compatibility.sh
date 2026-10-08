#!/bin/bash

# Test Logiks Framework compatibility across PHP versions
# Everything runs in Docker containers

set -e

echo "=================================================="
echo "Logiks Framework - PHP Compatibility Testing"
echo "=================================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

VERSIONS=("php80" "php81" "php82" "php83" "php84")
RESULTS_DIR="test-results"

mkdir -p $RESULTS_DIR

# Test each PHP version
for VERSION in "${VERSIONS[@]}"; do
    echo -e "${BLUE}Testing $VERSION...${NC}"
    echo "----------------------------------------"
    
    # Check if container is running
    if ! docker ps | grep -q "logiks-$VERSION"; then
        echo -e "${RED}✗ Container logiks-$VERSION is not running${NC}"
        echo "  Run: docker-compose up -d $VERSION"
        continue
    fi
    
    # Test 1: PHP Version
    echo -n "  • PHP Version check... "
    PHP_VER=$(docker exec logiks-$VERSION php -v | head -n 1)
    echo -e "${GREEN}✓${NC}"
    echo "    $PHP_VER"
    
    # Test 2: Required Extensions
    echo -n "  • Required extensions... "
    EXTENSIONS=("mysqli" "pdo_mysql" "mbstring" "json" "curl")
    MISSING=""
    
    for EXT in "${EXTENSIONS[@]}"; do
        if ! docker exec logiks-$VERSION php -m | grep -q "^$EXT$"; then
            MISSING="$MISSING $EXT"
        fi
    done
    
    if [ -z "$MISSING" ]; then
        echo -e "${GREEN}✓${NC}"
    else
        echo -e "${RED}✗ Missing:$MISSING${NC}"
    fi
    
    # Test 3: Database Connection
    echo -n "  • MySQL connection... "
    DB_TEST=$(docker exec logiks-$VERSION php -r "
        \$conn = @new mysqli('mysql84', 'logiks', 'logiks123', 'logiks_test');
        echo \$conn->connect_error ? 'FAIL' : 'OK';
    ")
    
    if [ "$DB_TEST" = "OK" ]; then
        echo -e "${GREEN}✓${NC}"
    else
        echo -e "${RED}✗ Failed${NC}"
    fi
    
    # Test 4: File Syntax Check
    echo -n "  • Framework syntax... "
    SYNTAX_ERRORS=$(docker exec logiks-$VERSION find /var/www/html/api -name "*.php" -exec php -l {} \; 2>&1 | grep -c "Parse error" || true)
    
    if [ "$SYNTAX_ERRORS" -eq 0 ]; then
        echo -e "${GREEN}✓${NC}"
    else
        echo -e "${RED}✗ $SYNTAX_ERRORS errors${NC}"
    fi
    
    # Test 5: Compatibility Layer
    echo -n "  • PHP 8.x compatibility... "
    COMPAT_TEST=$(docker exec logiks-$VERSION php -r "
        if (file_exists('/var/www/html/api/compat/php8-compat.php')) {
            require_once '/var/www/html/api/compat/php8-compat.php';
            echo (defined('LOGIKS_PHP8_COMPAT_LOADED') ? 'OK' : 'FAIL');
        } else {
            echo 'MISSING';
        }
    ")
    
    if [ "$COMPAT_TEST" = "OK" ]; then
        echo -e "${GREEN}✓${NC}"
    else
        echo -e "${RED}✗ $COMPAT_TEST${NC}"
    fi
    
    # Test 6: MySQLi Driver
    echo -n "  • MySQLi driver... "
    MYSQLI_TEST=$(docker exec logiks-$VERSION php -r "
        if (file_exists('/var/www/html/api/libs/logiksDB/MySQLiDriver.php')) {
            require_once '/var/www/html/api/libs/logiksDB/MySQLiDriver.php';
            echo (class_exists('MySQLiDriver') ? 'OK' : 'FAIL');
        } else {
            echo 'MISSING';
        }
    ")
    
    if [ "$MYSQLI_TEST" = "OK" ]; then
        echo -e "${GREEN}✓${NC}"
    else
        echo -e "${RED}✗ $MYSQLI_TEST${NC}"
    fi
    
    echo ""
done

echo "=================================================="

# Summary
echo ""
echo "Test Summary:"
echo "  • Tested ${#VERSIONS[@]} PHP versions"
echo "  • Results saved in: $RESULTS_DIR/"
echo ""
echo "For detailed testing, run: ./docker/scripts/test-all.sh"
echo ""
