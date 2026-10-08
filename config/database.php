<?php
/**
 * config/database.php
 * Koneksi PDO dengan Singleton pattern.
 * Kredensial dapat di-override lewat environment variable (DB_HOST, DB_NAME, DB_USER, DB_PASS).
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $conn;

    // Private: tidak bisa di-new dari luar
    private function __construct()
    {
        $host = getenv('DB_HOST') ?: 'localhost';
        $name = getenv('DB_NAME') ?: 'inventaris_db';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // error -> exception
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // hasil = array asosiatif
            PDO::ATTR_EMULATE_PREPARES   => false,                    // real prepared statement
        ];

        try {
            $this->conn = new PDO(
                "mysql:host=$host;dbname=$name;charset=utf8mb4",
                $user,
                $pass,
                $options
            );
        } catch (PDOException $e) {
            error_log('Koneksi DB gagal: ' . $e->getMessage());
            die('Koneksi database gagal. Pastikan MySQL aktif dan schema.sql sudah di-import.');
        }
    }

    // Mencegah clone / unserialize (menjaga hanya 1 instance)
    private function __clone() {}
    public function __wakeup() { throw new Exception('Cannot unserialize singleton'); }

    // Satu-satunya cara mengambil instance
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->conn;
    }
}
