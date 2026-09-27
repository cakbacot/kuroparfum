<?php
// ===================================================
// Kuro Atelier - Database Connection (PDO)
// Supports Port 3307 (MariaDB XAMPP) & 3306 (Standard MySQL)
// ===================================================

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $host = '127.0.0.1';
        $dbName = 'kuro_ecommerce';
        $user = 'root';
        $pass = '';

        // Priority 1: Check port 3307 (active running instance)
        // Priority 2: Fallback to 3306 (default XAMPP/MySQL)
        $portsToTry = [3307, 3306];
        $lastException = null;

        foreach ($portsToTry as $port) {
            try {
                $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ];

                self::$instance = new PDO($dsn, $user, $pass, $options);
                return self::$instance;
            } catch (PDOException $e) {
                $lastException = $e;
            }
        }

        // If connection fails on all ports, return error JSON
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Gagal terhubung ke database MySQL. Pastikan MySQL berjalan di port 3307 atau 3306.',
            'error'   => $lastException ? $lastException->getMessage() : 'Unknown database error'
        ]);
        exit;
    }
}
