#!/bin/bash

# Quick test script that doesn't require Docker
# Tests the PHP compatibility layer and code directly

echo "=============================================="
echo "Logiks Framework - Quick Compatibility Test"
echo "=============================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

cd /Users/bismaymohapatra/Workspace/Active/Logiks-PHP/Code/Logiks-Core

# Test 1: Check if compatibility layer exists
echo -n "1. Compatibility layer... "
if [ -f "api/compat/php8-compat.php" ]; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗ Missing${NC}"
    exit 1
fi

# Test 2: Check if MySQLi driver exists
echo -n "2. MySQLi driver... "
if [ -f "api/libs/logiksDB/MySQLiDriver.php" ]; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗ Missing${NC}"
    exit 1
fi

# Test 3: Check PHP syntax of compatibility layer
echo -n "3. Syntax check (compatibility)... "
if php -l api/compat/php8-compat.php > /dev/null 2>&1; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗ Syntax error${NC}"
    exit 1
fi

# Test 4: Check PHP syntax of MySQLi driver
echo -n "4. Syntax check (MySQLi driver)... "
if php -l api/libs/logiksDB/MySQLiDriver.php > /dev/null 2>&1; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗ Syntax error${NC}"
    exit 1
fi

# Test 5: Check core framework files
echo -n "5. Core framework syntax... "
ERROR_COUNT=0
for file in api/initialize.php api/commons.php api/configurator.php; do
    if ! php -l "$file" > /dev/null 2>&1; then
        ERROR_COUNT=$((ERROR_COUNT + 1))
    fi
done

if [ $ERROR_COUNT -eq 0 ]; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗ $ERROR_COUNT errors${NC}"
fi

# Test 6: Test compatibility layer loading
echo -n "6. Compatibility layer loading... "
php -r "
    require_once '/Users/bismaymohapatra/Workspace/Active/Logiks-PHP/Code/Logiks-Core/api/compat/php8-compat.php';
    if (defined('LOGIKS_PHP8_COMPAT_LOADED')) {
        exit(0);
    } else {
        exit(1);
    }
" > /dev/null 2>&1

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗ Failed${NC}"
fi

# Test 7: Test MYSQL constants
echo -n "7. MYSQL_* constants... "
php -r "
    require_once '/Users/bismaymohapatra/Workspace/Active/Logiks-PHP/Code/Logiks-Core/api/compat/php8-compat.php';
    if (defined('MYSQL_ASSOC') && defined('MYSQL_NUM') && defined('MYSQL_BOTH')) {
        exit(0);
    } else {
        exit(1);
    }
" > /dev/null 2>&1

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗ Failed${NC}"
fi

# Test 8: Test MySQLi driver class
echo -n "8. MySQLi driver class... "
php -r "
    require_once '/Users/bismaymohapatra/Workspace/Active/Logiks-PHP/Code/Logiks-Core/api/libs/logiksDB/AbstractDBDriver.php';
    require_once '/Users/bismaymohapatra/Workspace/Active/Logiks-PHP/Code/Logiks-Core/api/libs/logiksDB/MySQLiDriver.php';
    if (class_exists('MySQLiDriver')) {
        exit(0);
    } else {
        exit(1);
    }
" > /dev/null 2>&1

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗ Failed${NC}"
fi

# Test 9: Check Docker files
echo -n "9. Docker configuration... "
if [ -f "docker-compose.yml" ] && [ -d "docker/scripts" ]; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗ Missing${NC}"
fi

# Test 10: Check documentation
echo -n "10. Documentation... "
if [ -f "QUICKSTART.md" ] && [ -f "UPGRADE.md" ] && [ -f "docker/README.md" ]; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${YELLOW}⚠ Incomplete${NC}"
fi

echo ""
echo "=============================================="
echo -e "${GREEN}Quick Test Complete!${NC}"
echo "=============================================="
echo ""
echo "Your PHP version:"
php -v | head -1
echo ""
echo "Next steps:"
echo "  • To test with Docker: ./docker/scripts/setup.sh"
echo "  • To test locally: Run your PHP server"
echo ""
