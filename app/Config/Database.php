<?php
// app/Config/Database.php
class Database {
    private $host = 'localhost';
    private $db = 'publicidad';
    private $user = 'root';
    private $pass = '';
    private $charset = 'utf8mb4';
    private $pdo;

    public function connect(){
        if ($this->pdo) return $this->pdo;
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $opt);
            return $this->pdo;
        } catch (PDOException $e) {
            // Mensaje claro para desarrollo; en producción loggear y mostrar mensaje genérico
            die("DB Error: " . $e->getMessage());
        }
    }
}
