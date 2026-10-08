#!/bin/bash

# Test MySQL 8.4 compatibility

echo "=================================================="
echo "MySQL 8.4 Compatibility Test"
echo "=================================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Check if MySQL is running
if ! docker ps | grep -q "logiks-mysql84"; then
    echo -e "${RED}✗ MySQL 8.4 container is not running${NC}"
    exit 1
fi

echo "MySQL Version:"
docker exec logiks-mysql84 mysql -V
echo ""

echo "Testing MySQL connection..."
docker exec logiks-mysql84 mysql -ulogiks -plogiks123 logiks_test -e "SELECT VERSION();" 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Connection successful${NC}"
else
    echo -e "${RED}✗ Connection failed${NC}"
    exit 1
fi
echo ""

echo "Checking SQL Mode:"
docker exec logiks-mysql84 mysql -ulogiks -plogiks123 logiks_test -e "SELECT @@sql_mode;" 2>/dev/null
echo ""

echo "Checking Character Set:"
docker exec logiks-mysql84 mysql -ulogiks -plogiks123 logiks_test -e "SHOW VARIABLES LIKE 'char%';" 2>/dev/null
echo ""

echo "Testing utf8mb4 support:"
docker exec logiks-mysql84 mysql -ulogiks -plogiks123 logiks_test -e "
    CREATE TABLE IF NOT EXISTS emoji_test (
        id INT AUTO_INCREMENT PRIMARY KEY,
        content VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    
    INSERT INTO emoji_test (content) VALUES ('Hello 👋 World 🌍');
    SELECT * FROM emoji_test;
    DROP TABLE emoji_test;
" 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ utf8mb4 support working${NC}"
else
    echo -e "${RED}✗ utf8mb4 test failed${NC}"
fi
echo ""

echo "Testing JSON datatype (MySQL 8.x feature):"
docker exec logiks-mysql84 mysql -ulogiks -plogiks123 logiks_test -e "
    CREATE TABLE IF NOT EXISTS json_test (
        id INT AUTO_INCREMENT PRIMARY KEY,
        data JSON
    ) ENGINE=InnoDB;
    
    INSERT INTO json_test (data) VALUES ('{\"name\":\"test\",\"value\":123}');
    SELECT JSON_EXTRACT(data, '$.name') as name FROM json_test;
    DROP TABLE json_test;
" 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ JSON support working${NC}"
else
    echo -e "${RED}✗ JSON test failed${NC}"
fi
echo ""

echo "Testing Window Functions (MySQL 8.x feature):"
docker exec logiks-mysql84 mysql -ulogiks -plogiks123 logiks_test -e "
    CREATE TABLE IF NOT EXISTS window_test (
        id INT,
        value INT
    ) ENGINE=InnoDB;
    
    INSERT INTO window_test VALUES (1, 10), (2, 20), (3, 30);
    SELECT id, value, ROW_NUMBER() OVER (ORDER BY id) as row_num FROM window_test;
    DROP TABLE window_test;
" 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Window functions working${NC}"
else
    echo -e "${RED}✗ Window functions test failed${NC}"
fi
echo ""

echo "=================================================="
echo -e "${GREEN}MySQL 8.4 compatibility test completed${NC}"
echo "=================================================="
