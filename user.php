<?php
class User {
    private $db; // Connexion à la base de données
    private $email;
    private $password;

    public function __construct($db) {
        $this->db = $db;
    }

    public function login($email, $password) {
        // Préparer la requête pour récupérer l'utilisateur par email
        $query = "SELECT * FROM utilisateurs WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifier si l'utilisateur existe et si le mot de passe correspond
        if ($user && password_verify($password, $user['password'])) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            return true;
        }
        return false;
    }
}
?>