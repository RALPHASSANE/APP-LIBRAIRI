<?php
session_start();

// 1. Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Si l'utilisateur est déjà connecté, redirection vers la page d'accueil
if (isset($_SESSION['user_id'])) {
    header("Location: accueil.php");
    exit();
}

$error = null;

// 3. Vérifier si le formulaire de connexion a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'], $_POST['password'])) {
    
    // CORRECTION : Utilisation de require_once et __DIR__
    require_once __DIR__ . '/database.php';
    $database = new Database();
    $conn = $database->getConnection();

    $email = $_POST['email'];
    $password = $_POST['password'];

    // Préparation de la requête
    $query = "SELECT * FROM users WHERE email = :email";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérification du mot de passe
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        header("Location: accueil.php");
        exit();
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - LIBRAIRIE</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <h1>Connexion</h1>
        <p>Veuillez entrer vos identifiants pour accéder à votre bibliothèque.</p>

        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form action="index.php" method="POST">
            <input type="email" name="email" placeholder="Votre email" required>
            <input type="password" name="password" placeholder="Votre mot de passe" required>
            <button type="submit">Se connecter</button>
        </form>

        <p class="register-link">Pas encore inscrit ? <a href="inscription.php">Créer un compte</a></p>
    </div>
</body>
</html>
