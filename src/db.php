<?php
class DB {
    private static $connection = null;

    public static function connect() {
        if (!self::$connection) {
            try {
                self::$connection = new PDO(
                    "pgsql:host=" . getenv('DB_HOST') . 
                    ";dbname=" . getenv('DB_NAME'),
                    getenv('DB_USER'),
                    getenv('DB_PASSWORD')
                );
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch(PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}
?>