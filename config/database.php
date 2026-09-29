<?php
/**
 * Database Configuration & Connection
 * Khodiyar Computer - IT Services
 */

// Database Credentials
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'khodiyar_computer');

/**
 * Get PDO Database Connection
 */
function getConnection() {
    static $conn = null;
    if ($conn === null) {
        try {
            $conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            die("Database Connection Failed: " . $e->getMessage());
        }
    }
    return $conn;
}

/**
 * Execute a query with parameters
 */
function dbQuery($sql, $params = []) {
    $stmt = getConnection()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch single row
 */
function dbFetchOne($sql, $params = []) {
    return dbQuery($sql, $params)->fetch();
}

/**
 * Fetch all rows
 */
function dbFetchAll($sql, $params = []) {
    return dbQuery($sql, $params)->fetchAll();
}

/**
 * Insert and return last insert ID
 */
function dbInsert($sql, $params = []) {
    dbQuery($sql, $params);
    return getConnection()->lastInsertId();
}

/**
 * Get row count
 */
function dbCount($sql, $params = []) {
    return dbQuery($sql, $params)->rowCount();
}

