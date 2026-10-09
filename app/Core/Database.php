<?php
class Database {
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . (defined('DB_PORT') ? ";port=" . DB_PORT : "") . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            // Log the detail server-side; never show connection details to visitors.
            error_log("Connection Error: " . $e->getMessage());
            die("Database connection failed. Please check logs.");
        }

        return $this->conn;
    }
}
