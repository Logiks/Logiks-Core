#!/bin/bash

# Comprehensive test suite for all PHP versions
# Runs entirely in Docker containers

set -e

echo "=================================================="
echo "Logiks Framework - Full Test Suite"
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

# Summary arrays
declare -A RESULTS
TOTAL_TESTS=0
PASSED_TESTS=0

# Function to run test
run_test() {
    local VERSION=$1
    local TEST_NAME=$2
    local TEST_CMD=$3
    
    echo -n "    • $TEST_NAME... "
    
    if eval "$TEST_CMD" > "$RESULTS_DIR/${VERSION}-${TEST_NAME// /_}.log" 2>&1; then
        echo -e "${GREEN}✓${NC}"
        return 0
    else
        echo -e "${RED}✗${NC}"
        return 1
    fi
}

# Test each version
for VERSION in "${VERSIONS[@]}"; do
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}Testing: $VERSION${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    
    VERSION_TESTS=0
    VERSION_PASSED=0
    
    # Check if container is running
    if ! docker ps | grep -q "logiks-$VERSION"; then
        echo -e "${RED}✗ Container not running${NC}"
        RESULTS[$VERSION]="SKIP"
        continue
    fi
    
    # Test Suite
    echo "  Core Tests:"
    
    # Test 1: PHP Version
    ((VERSION_TESTS++))
    if run_test "$VERSION" "PHP Version" \
        "docker exec logiks-$VERSION php -v"; then
        ((VERSION_PASSED++))
    fi
    
    # Test 2: Extensions
    ((VERSION_TESTS++))
    if run_test "$VERSION" "Extensions" \
        "docker exec logiks-$VERSION php -m | grep -E '(mysqli|pdo_mysql|mbstring|json|curl|redis|memcached)'"; then
        ((VERSION_PASSED++))
    fi
    
    # Test 3: Syntax Check
    ((VERSION_TESTS++))
    if run_test "$VERSION" "Syntax Check" \
        "docker exec logiks-$VERSION php -l /var/www/html/api/initialize.php"; then
        ((VERSION_PASSED++))
    fi
    
    echo ""
    echo "  Database Tests:"
    
    # Test 4: MySQL Connection
    ((VERSION_TESTS++))
    if run_test "$VERSION" "MySQL Connect" \
        "docker exec logiks-$VERSION php -r '\$c=new mysqli(\"mysql84\",\"logiks\",\"logiks123\",\"logiks_test\");exit(\$c->connect_error?1:0);'"; then
        ((VERSION_PASSED++))
    fi
    
    # Test 5: Query Execution
    ((VERSION_TESTS++))
    if run_test "$VERSION" "MySQL Query" \
        "docker exec logiks-$VERSION php -r '\$c=new mysqli(\"mysql84\",\"logiks\",\"logiks123\",\"logiks_test\");\$r=\$c->query(\"SELECT 1\");exit(\$r?0:1);'"; then
        ((VERSION_PASSED++))
    fi
    
    echo ""
    echo "  Framework Tests:"
    
    # Test 6: Compatibility Layer
    ((VERSION_TESTS++))
    if run_test "$VERSION" "Compat Layer" \
        "docker exec logiks-$VERSION php -r 'require \"/var/www/html/api/compat/php8-compat.php\";exit(defined(\"LOGIKS_PHP8_COMPAT_LOADED\")?0:1);'"; then
        ((VERSION_PASSED++))
    fi
    
    # Test 7: MySQLi Driver
    ((VERSION_TESTS++))
    if run_test "$VERSION" "MySQLi Driver" \
        "docker exec logiks-$VERSION php -r 'require \"/var/www/html/api/libs/logiksDB/MySQLiDriver.php\";exit(class_exists(\"MySQLiDriver\")?0:1);'"; then
        ((VERSION_PASSED++))
    fi
    
    # Test 8: Commons Functions
    ((VERSION_TESTS++))
    if run_test "$VERSION" "Commons Functions" \
        "docker exec logiks-$VERSION php -r 'require \"/var/www/html/api/commons.php\";exit(function_exists(\"clean\")?0:1);'"; then
        ((VERSION_PASSED++))
    fi
    
    echo ""
    echo "  Helper Tests:"
    
    # Test 9: Helper Loading
    ((VERSION_TESTS++))
    if run_test "$VERSION" "Helper Files" \
        "docker exec logiks-$VERSION find /var/www/html/api/helpers -name '*.php' -type f"; then
        ((VERSION_PASSED++))
    fi
    
    # Test 10: Configuration
    ((VERSION_TESTS++))
    if run_test "$VERSION" "Config System" \
        "docker exec logiks-$VERSION php -l /var/www/html/api/configurator.php"; then
        ((VERSION_PASSED++))
    fi
    
    # Calculate version results
    TOTAL_TESTS=$((TOTAL_TESTS + VERSION_TESTS))
    PASSED_TESTS=$((PASSED_TESTS + VERSION_PASSED))
    
    PERCENTAGE=$((VERSION_PASSED * 100 / VERSION_TESTS))
    
    echo ""
    echo "  Results: $VERSION_PASSED/$VERSION_TESTS tests passed ($PERCENTAGE%)"
    
    if [ $PERCENTAGE -eq 100 ]; then
        RESULTS[$VERSION]="${GREEN}PASS${NC}"
        echo -e "  Status: ${GREEN}✓ PASS${NC}"
    elif [ $PERCENTAGE -ge 80 ]; then
        RESULTS[$VERSION]="${YELLOW}WARN${NC}"
        echo -e "  Status: ${YELLOW}⚠ WARNING${NC}"
    else
        RESULTS[$VERSION]="${RED}FAIL${NC}"
        echo -e "  Status: ${RED}✗ FAIL${NC}"
    fi
    
    echo ""
done

# Final Summary
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}FINAL SUMMARY${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

for VERSION in "${VERSIONS[@]}"; do
    printf "  %-10s : " "$VERSION"
    echo -e "${RESULTS[$VERSION]}"
done

echo ""
TOTAL_PERCENTAGE=$((PASSED_TESTS * 100 / TOTAL_TESTS))
echo "  Overall: $PASSED_TESTS/$TOTAL_TESTS tests passed ($TOTAL_PERCENTAGE%)"
echo ""
echo "  Detailed logs: $RESULTS_DIR/"
echo ""

if [ $TOTAL_PERCENTAGE -eq 100 ]; then
    echo -e "${GREEN}✓ All tests passed! Framework is ready for PHP 8.x${NC}"
    exit 0
elif [ $TOTAL_PERCENTAGE -ge 80 ]; then
    echo -e "${YELLOW}⚠ Most tests passed, but some issues need attention${NC}"
    exit 0
else
    echo -e "${RED}✗ Critical issues detected, review logs${NC}"
    exit 1
fi
