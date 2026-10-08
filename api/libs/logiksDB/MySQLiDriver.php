<?php
/**
 * MySQLi Database Driver for Logiks Framework
 * 
 * Provides PHP 8.x and MySQL 8.4 compatible database operations
 * 
 * @package    Logiks
 * @subpackage Database
 * @author     Logiks Development Team
 * @version    4.2
 * @since      PHP 7.4
 */

if(!defined('ROOT')) exit('No direct script access allowed');

require_once dirname(__FILE__) . '/AbstractDBDriver.php';

class MySQLiDriver extends AbstractDBDriver {
    
    private $connection = null;
    private $host;
    private $username;
    private $password;
    private $database;
    private $port;
    private $socket;
    private $charset;
    private $lastError = '';
    
    /**
     * Constructor
     * 
     * @param string $keyName Database connection key
     * @param array $params Connection parameters
     */
    public function __construct($keyName, $params) {
        parent::__construct($keyName, $params);
        
        $this->host = $params['host'] ?? 'localhost';
        $this->username = $params['username'] ?? '';
        $this->password = $params['password'] ?? '';
        $this->database = $params['database'] ?? '';
        $this->port = $params['port'] ?? 3306;
        $this->socket = $params['socket'] ?? null;
        $this->charset = $params['charset'] ?? 'utf8mb4';
    }
    
    /**
     * Open database connection
     * 
     * @param string $keyName Connection key
     * @return bool Success status
     */
    public function open($keyName) {
        try {
            // Enable exception mode for better error handling in PHP 8.x
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            
            // Create connection
            $this->connection = new mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->database,
                $this->port,
                $this->socket
            );
            
            // Check connection
            if ($this->connection->connect_error) {
                $this->lastError = $this->connection->connect_error;
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            // Set charset (utf8mb4 for full UTF-8 support including emojis)
            if (!$this->connection->set_charset($this->charset)) {
                $this->lastError = "Error loading character set {$this->charset}: " . $this->connection->error;
                trigger_logikserror($this->lastError, E_USER_WARNING);
            }
            
            // MySQL 8.x specific settings
            if ($this->getMySQLVersion() >= 80000) {
                // Set SQL mode for compatibility
                $this->connection->query("SET SESSION sql_mode = 'TRADITIONAL'");
                
                // Ensure proper collation for MySQL 8.x
                if ($this->charset === 'utf8mb4') {
                    $this->connection->query("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
                }
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            if (function_exists('trigger_logikserror')) {
                trigger_logikserror("Database connection failed [{$keyName}]: " . $e->getMessage(), E_USER_ERROR);
            } else {
                error_log("Database connection failed [{$keyName}]: " . $e->getMessage());
            }
            return false;
        }
    }
    
    /**
     * Close database connection
     * 
     * @return bool Success status
     */
    public function close() {
        if ($this->connection !== null) {
            try {
                $this->connection->close();
                $this->connection = null;
                return true;
            } catch (Exception $e) {
                $this->lastError = $e->getMessage();
                return false;
            }
        }
        return true;
    }
    
    /**
     * Execute a query
     * 
     * @param string $sql SQL query
     * @return mixed Query result or false on failure
     */
    public function query($sql) {
        if ($this->connection === null) {
            $this->lastError = "No database connection";
            return false;
        }
        
        try {
            $result = $this->connection->query($sql);
            
            if ($result === false) {
                $this->lastError = $this->connection->error;
                if (function_exists('trigger_logikserror')) {
                    trigger_logikserror("Query failed: " . $this->connection->error . "\nSQL: " . $sql, E_USER_WARNING);
                }
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            if (function_exists('trigger_logikserror')) {
                trigger_logikserror("Query exception: " . $e->getMessage() . "\nSQL: " . $sql, E_USER_WARNING);
            }
            return false;
        }
    }
    
    /**
     * Escape string for safe SQL insertion
     * 
     * @param string $string String to escape
     * @return string Escaped string
     */
    public function escapeString($string) {
        if ($this->connection === null) {
            // Fallback when no connection exists
            return addslashes($string);
        }
        
        return $this->connection->real_escape_string($string);
    }
    
    /**
     * Fetch data from result
     * 
     * @param mixed $result Query result
     * @param string $type Fetch type: 'assoc', 'array', 'object'
     * @param int $mode Fetch mode constant
     * @return mixed Fetched row or false
     */
    public function fetchData($result, $type = "assoc", $mode = MYSQLI_ASSOC) {
        if (!$result || !($result instanceof mysqli_result)) {
            return false;
        }
        
        try {
            switch ($type) {
                case "array":
                    return $result->fetch_array($mode);
                    
                case "object":
                    return $result->fetch_object();
                    
                case "assoc":
                default:
                    return $result->fetch_assoc();
            }
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }
    
    /**
     * Fetch all rows from result
     * 
     * @param mixed $result Query result
     * @param string $type Fetch type
     * @param int $mode Fetch mode constant
     * @return array All rows
     */
    public function fetchAll($result, $type = "assoc", $mode = MYSQLI_ASSOC) {
        if (!$result || !($result instanceof mysqli_result)) {
            return [];
        }
        
        $rows = [];
        while ($row = $this->fetchData($result, $type, $mode)) {
            $rows[] = $row;
        }
        
        return $rows;
    }
    
    /**
     * Get number of rows in result
     * 
     * @param mixed $result Query result
     * @return int Number of rows
     */
    public function numRows($result) {
        if (!$result || !($result instanceof mysqli_result)) {
            return 0;
        }
        
        return $result->num_rows;
    }
    
    /**
     * Get number of affected rows
     * 
     * @return int Number of affected rows
     */
    public function affectedRows() {
        if ($this->connection === null) {
            return 0;
        }
        
        return $this->connection->affected_rows;
    }
    
    /**
     * Get last insert ID
     * 
     * @return int Last insert ID
     */
    public function insertId() {
        if ($this->connection === null) {
            return 0;
        }
        
        return $this->connection->insert_id;
    }
    
    /**
     * Get number of fields in result
     * 
     * @param mixed $result Query result
     * @return int Number of fields
     */
    public function numFields($result) {
        if (!$result || !($result instanceof mysqli_result)) {
            return 0;
        }
        
        return $result->field_count;
    }
    
    /**
     * Get field information
     * 
     * @param mixed $result Query result
     * @param int $field_offset Field offset
     * @return object|false Field info object
     */
    public function fetchField($result, $field_offset) {
        if (!$result || !($result instanceof mysqli_result)) {
            return false;
        }
        
        return $result->fetch_field_direct($field_offset);
    }
    
    /**
     * Get field type name
     * 
     * @param mixed $result Query result
     * @param int $field_offset Field offset
     * @return string|false Field type name
     */
    public function getFieldType($result, $field_offset) {
        $field_info = $this->fetchField($result, $field_offset);
        
        if (!$field_info) {
            return false;
        }
        
        return $this->getMySQLFieldTypeName($field_info->type);
    }
    
    /**
     * Get field length
     * 
     * @param mixed $result Query result
     * @param int $field_offset Field offset
     * @return int|false Field length
     */
    public function getFieldLength($result, $field_offset) {
        $field_info = $this->fetchField($result, $field_offset);
        
        if (!$field_info) {
            return false;
        }
        
        return $field_info->length;
    }
    
    /**
     * Free result memory
     * 
     * @param mixed $result Query result
     * @return bool Success status
     */
    public function freeResult($result) {
        if ($result && $result instanceof mysqli_result) {
            try {
                $result->free();
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }
    
    /**
     * Get last error message
     * 
     * @return string Error message
     */
    public function getLastError() {
        if ($this->connection !== null) {
            return $this->connection->error;
        }
        return $this->lastError;
    }
    
    /**
     * Get MySQL version as integer (e.g., 80400 for 8.4.0)
     * 
     * @return int MySQL version
     */
    public function getMySQLVersion() {
        if ($this->connection === null) {
            return 0;
        }
        
        return $this->connection->server_version;
    }
    
    /**
     * Get MySQL version as string
     * 
     * @return string MySQL version string
     */
    public function getMySQLVersionString() {
        if ($this->connection === null) {
            return 'Unknown';
        }
        
        return $this->connection->server_info;
    }
    
    /**
     * Begin transaction
     * 
     * @return bool Success status
     */
    public function beginTransaction() {
        if ($this->connection === null) {
            return false;
        }
        
        return $this->connection->begin_transaction();
    }
    
    /**
     * Commit transaction
     * 
     * @return bool Success status
     */
    public function commit() {
        if ($this->connection === null) {
            return false;
        }
        
        return $this->connection->commit();
    }
    
    /**
     * Rollback transaction
     * 
     * @return bool Success status
     */
    public function rollback() {
        if ($this->connection === null) {
            return false;
        }
        
        return $this->connection->rollback();
    }
    
    /**
     * Convert MySQL field type ID to name
     * 
     * @param int $type_id MySQL type constant
     * @return string Type name
     */
    private function getMySQLFieldTypeName($type_id) {
        $types = [
            MYSQLI_TYPE_DECIMAL     => 'decimal',
            MYSQLI_TYPE_TINY        => 'tinyint',
            MYSQLI_TYPE_SHORT       => 'smallint',
            MYSQLI_TYPE_LONG        => 'int',
            MYSQLI_TYPE_FLOAT       => 'float',
            MYSQLI_TYPE_DOUBLE      => 'double',
            MYSQLI_TYPE_TIMESTAMP   => 'timestamp',
            MYSQLI_TYPE_LONGLONG    => 'bigint',
            MYSQLI_TYPE_INT24       => 'mediumint',
            MYSQLI_TYPE_DATE        => 'date',
            MYSQLI_TYPE_TIME        => 'time',
            MYSQLI_TYPE_DATETIME    => 'datetime',
            MYSQLI_TYPE_YEAR        => 'year',
            MYSQLI_TYPE_NEWDATE     => 'date',
            MYSQLI_TYPE_VARCHAR     => 'varchar',
            MYSQLI_TYPE_BIT         => 'bit',
            MYSQLI_TYPE_JSON        => 'json',
            MYSQLI_TYPE_NEWDECIMAL  => 'decimal',
            MYSQLI_TYPE_ENUM        => 'enum',
            MYSQLI_TYPE_SET         => 'set',
            MYSQLI_TYPE_TINY_BLOB   => 'tinyblob',
            MYSQLI_TYPE_MEDIUM_BLOB => 'mediumblob',
            MYSQLI_TYPE_LONG_BLOB   => 'longblob',
            MYSQLI_TYPE_BLOB        => 'blob',
            MYSQLI_TYPE_VAR_STRING  => 'varchar',
            MYSQLI_TYPE_STRING      => 'char',
            MYSQLI_TYPE_GEOMETRY    => 'geometry',
        ];
        
        return $types[$type_id] ?? 'unknown';
    }
    
    /**
     * Check if connection is alive
     * 
     * @return bool Connection status
     */
    public function ping() {
        if ($this->connection === null) {
            return false;
        }
        
        try {
            return $this->connection->ping();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get connection statistics
     * 
     * @return array Connection stats
     */
    public function getStats() {
        if ($this->connection === null) {
            return [];
        }
        
        return [
            'host' => $this->host,
            'database' => $this->database,
            'charset' => $this->charset,
            'server_info' => $this->connection->server_info,
            'server_version' => $this->connection->server_version,
            'protocol_version' => $this->connection->protocol_version,
            'host_info' => $this->connection->host_info,
            'thread_id' => $this->connection->thread_id,
        ];
    }
}
