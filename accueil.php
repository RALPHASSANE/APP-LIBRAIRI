<?php
session_start();

// 1. Activer l'affichage des erreurs (Utile en développement)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Vérifier si l'utilisateur est connecté
// CORRECTION : On redirige vers login.php (et pas index.php) pour éviter une boucle infinie
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 3. Connexion à la base de données avec chemin absolu
require_once __DIR__ . '/database.php';
$database = new Database();
$conn = $database->getConnection();

// 4. Inclusion de la clé API avec chemin absolu
// Rappel : ce fichier doit être présent localement mais ignoré par Git
require_once __DIR__ . '/config.php';

$results = [];
$error = null;
$search_performed = false;

// 5. Logique de recherche Google Books
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['searchQuery'])) {
    $search_performed = true;
    $searchQuery = urlencode($_POST['searchQuery']);
    $apiUrl = "https://www.googleapis.com/books/v1/volumes?q={$searchQuery}&maxResults=20&langRestrict=fr&key={$apiKey}";

    // Effectuer la requête à l'API
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
<body class="<?php if ($search_performed) echo 'search-active'; ?>">
    <div class="container">
        <div class="navbar">
            <a href="index.php">Accueil</a>
            <a href="mes_favoris.php">Mes Favoris</a>
            <a href="profil.php">Profil</a>
            <a href="logout.php">Se Déconnecter</a>
        </div>

        <h1>Bienvenue dans votre bibliothèque</h1>

        <form action="index.php" method="POST" class="search-form">
            <input type="text" name="searchQuery" placeholder="Rechercher un livre..." required>
            <button type="submit">Rechercher</button>
        </form>

        <?php if (!empty($results)): ?>
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

            <div class="results-footer">
                <a href="index.php" class="btn-back">Retour</a>
            </div>

        <?php elseif ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
