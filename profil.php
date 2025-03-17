<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Connexion à la base de données
require 'database.php';
$database = new Database();
$conn = $database->getConnection();

// Récupérer les informations de l'utilisateur
$userId = $_SESSION['user_id'];
$query = "SELECT username, email FROM users WHERE id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Initialiser les messages
$message = null;
$error = null;

// Mettre à jour les informations de l'utilisateur
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validation des champs
    if (empty($username) || empty($email)) {
        $error = "Le nom d'utilisateur et l'email sont obligatoires.";
    } else {
        try {
            // Construire la requête SQL dynamiquement
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateQuery = "UPDATE users SET username = :username, email = :email, password = :password WHERE id = :user_id";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            } else {
                $updateQuery = "UPDATE users SET username = :username, email = :email WHERE id = :user_id";
                $stmt = $conn->prepare($updateQuery);
            }

            // Lier les paramètres communs
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

            // Exécuter la requête
            if ($stmt->execute()) {
                $message = "Vos informations ont été mises à jour avec succès.";
                // Mettre à jour les informations de session si l'email a changé
                $_SESSION['user_email'] = $email;
            } else {
                $error = "Une erreur est survenue lors de la mise à jour.";
            }
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - LIBRAIRIE</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <!-- Barre de navigation -->
        <div class="navbar">
            <a href="accueil.php">Accueil</a>
            <a href="mes_favoris.php">Mes Favoris</a>
            <a href="profil.php">Profil</a>
            <a href="logout.php">Se Déconnecter</a>
        </div>

        <!-- Titre principal -->
        <h1>Mon Profil</h1>

        <!-- Message de confirmation ou d'erreur -->
        <?php if ($message): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php elseif ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <!-- Formulaire de modification du profil -->
        <form action="profil.php" method="POST" class="profile-form">
            <label for="username">Nom d'utilisateur :</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

            <label for="email">Email :</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label for="password">Nouveau mot de passe (laisser vide pour ne pas changer) :</label>
            <input type="password" id="password" name="password">

            <button type="submit">Mettre à jour</button>
        </form>
    </div>
</body>
</html>