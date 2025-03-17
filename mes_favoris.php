<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require 'database.php';
$database = new Database();
$conn = $database->getConnection();

// Supprimer un favori si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['favori_id'])) {
    $favoriId = $_POST['favori_id'];
    $userId = $_SESSION['user_id'];

    $query = "DELETE FROM favoris WHERE id = :favori_id AND user_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':favori_id', $favoriId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $message = "Le livre a été supprimé de vos favoris.";
    } else {
        $message = "Erreur lors de la suppression du livre.";
    }
}

// Afficher les messages de confirmation ou d'erreur
if (isset($_SESSION['success_message'])) {
    echo "<p style='color: green;'>" . $_SESSION['success_message'] . "</p>";
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo "<p style='color: red;'>" . $_SESSION['error_message'] . "</p>";
    unset($_SESSION['error_message']);
}

// Récupérer les favoris de l'utilisateur
$query = "SELECT * FROM favoris WHERE user_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Favoris - LIBRAIRIE</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="navbar">
            <a href="accueil.php">Accueil</a>
            <a href="mes_favoris.php">Mes Favoris</a>
            <a href="profil.php">Profil</a>
            <a href="logout.php">Se Déconnecter</a>
        </div>

        <h1>Mes Favoris</h1>

        <!-- Message de confirmation ou d'erreur -->
        <?php if (isset($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <div class="favorites-list">
            <?php if (!empty($favorites)): ?>
                <?php foreach ($favorites as $favorite): ?>
                    <div class="favorite-card">
                        <img src="<?php echo htmlspecialchars($favorite['image_url']); ?>" alt="<?php echo htmlspecialchars($favorite['title']); ?>">
                        <h3><?php echo htmlspecialchars($favorite['title']); ?></h3>
                        <p><?php echo htmlspecialchars($favorite['authors']); ?></p>
                        <form action="mes_favoris.php" method="POST">
                            <input type="hidden" name="favori_id" value="<?php echo htmlspecialchars($favorite['id']); ?>">
                            <button type="submit" class="btn-delete">Supprimer</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun favori trouvé.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
