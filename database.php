<?php
class Database {
    private $host = "127.0.0.1";  
    private $db_name = "library_db"; // ⚠️ Vérifie bien que ta base s'appelle comme ça dans phpMyAdmin
    private $username = "root";  
    private $password = "root";  
    private $port = "8889"; // 🚀 AJOUT : Le port par défaut de MAMP
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // On ajoute le port dans le DSN (mysql:host=...;port=...)
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";port=" . $this->port;
            
            $this->conn = new PDO($dsn, $this->username, $this->password);
            
            // 🚀 AJOUT : Force PDO à lancer des erreurs si la connexion échoue
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            // Si ça échoue, on arrête tout proprement pour comprendre pourquoi
            die("Erreur de connexion : " . $exception->getMessage());
        }
        return $this->conn;
    }
}
?>