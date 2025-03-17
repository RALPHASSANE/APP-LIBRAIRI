<?php
session_start();
require_once 'database.php';

// Tableau pour les erreurs
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation des données
    if (empty($username)) {
        $errors[] = "Le nom d'utilisateur est requis.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }

    if (empty($password)) {
        $errors[] = "Le mot de passe est requis.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    // Si pas d'erreurs, procéder à l'inscription
    if (empty($errors)) {
        try {
            $database = new Database();
            $conn = $database->getConnection();

            // Vérifier si l'email ou le nom d'utilisateur existe déjà
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email OR username = :username");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $errors[] = "Cet email ou nom d'utilisateur est déjà utilisé.";
            } else {
                // Hacher le mot de passe
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Préparer l'insertion dans la base de données
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, created_at, updated_at) 
                                        VALUES (:username, :email, :password, NOW(), NOW())");

                // Lier les paramètres
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);

                // Exécuter la requête
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Inscription réussie. Vous pouvez maintenant vous connecter.";
                    header('Location: index.php');
                    exit();
                } else {
                    $errors[] = "Erreur lors de l'inscription.";
                }
            }
        } catch(PDOException $e) {
            $errors[] = "Erreur de base de données : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - LIBRAIRIE</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Inscription</h1>

    <div class="signup-container">
        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="inscription.php" method="POST">
            <div>
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div>
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div>
                <button type="submit">S'inscrire</button>
            </div>
        </form>
    </div>
</body>
</html>
