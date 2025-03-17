<?php
session_start();

// Activer l'affichage des erreurs 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Connexion à la base de données
require 'database.php';
$database = new Database();
$conn = $database->getConnection();

$results = [];
$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['searchQuery'])) {
    $searchQuery = urlencode($_POST['searchQuery']);
    $apiUrl = "https://www.googleapis.com/books/v1/volumes?q={$searchQuery}&langRestrict=fr&key={$apiKey}";

    // Effectuer la requête à l'API Google Books
    $response = @file_get_contents($apiUrl);
    if ($response !== false) {
        $data = json_decode($response, true);
        if (isset($data['items'])) {
            $results = $data['items'];
        } else {
            $error = "Aucun résultat trouvé.";
        }
    } else {
        $error = "Erreur lors de la connexion à l'API Google Books.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - LIBRAIRIE</title>
    <link rel="stylesheet" href="style.css">
     
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
       <!-- navbar -->
        <div class="navbar">
            <a href="accueil.php">Accueil</a>
            <a href="mes_favoris.php">Mes Favoris</a>
            <a href="profil.php">Profil</a>
            <a href="logout.php">Se Déconnecter</a>
        </div>

        <!-- Titre principal -->
        <h1>Bienvenue dans votre bibliothèque</h1>

        <!-- Formulaire de recherche -->
        <form action="accueil.php" method="POST" class="search-form">
            <input type="text" name="searchQuery" placeholder="Rechercher un livre..." required>
            <button type="submit">Rechercher</button>
        </form>

        <!-- Section des résultats -->
        <?php if (!empty($results)): ?>
            <div class="results-header">
                
            </div>

            <div class="books-grid">
                <?php foreach ($results as $book): ?>
                    <div class="book-card">
                        <?php
                        $title = $book['volumeInfo']['title'] ?? 'Titre inconnu';
                        $authors = isset($book['volumeInfo']['authors']) ? implode(", ", $book['volumeInfo']['authors']) : 'Auteur inconnu';
                        $imageUrl = $book['volumeInfo']['imageLinks']['thumbnail'] ?? 'https://via.placeholder.com/150';
                        ?>
                        <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($title); ?>">
                        <h3><?php echo htmlspecialchars($title); ?></h3>
                        <p><?php echo htmlspecialchars($authors); ?></p>
                        <form action="ajouter_favori.php" method="POST">
                            <input type="hidden" name="bookId" value="<?php echo htmlspecialchars($book['id']); ?>">
                            <input type="hidden" name="title" value="<?php echo htmlspecialchars($title); ?>">
                            <input type="hidden" name="authors" value="<?php echo htmlspecialchars($authors); ?>">
                            <input type="hidden" name="imageUrl" value="<?php echo htmlspecialchars($imageUrl); ?>">
                            <button type="submit">Ajouter à ma bibliothèque</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Bouton Retour en bas de la page -->
            <div class="results-footer">
                <a href="accueil.php" class="btn-back">Retour</a>
            </div>
        <?php elseif ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
    </div>
</body>
</html>






