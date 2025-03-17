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

// Vérifier si les données du formulaire sont présentes
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['bookId'], $_POST['title'], $_POST['authors'], $_POST['imageUrl'])) {
    $userId = $_SESSION['user_id'];
    $bookId = $_POST['bookId'];
    $title = $_POST['title'];
    $authors = $_POST['authors'];
    $imageUrl = $_POST['imageUrl'];

    // Vérifier que les champs ne sont pas vides
    if (empty($title) || empty($bookId)) {
        die("Erreur : Les champs 'title' et 'bookId' sont obligatoires.");
    }

    // Préparer la requête d'insertion
    $query = "INSERT INTO favoris (user_id, book_id, title, authors, image_url) VALUES (:user_id, :book_id, :title, :authors, :image_url)";
    $stmt = $conn->prepare($query);

    // Lier les paramètres
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':book_id', $bookId);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':authors', $authors);
    $stmt->bindParam(':image_url', $imageUrl);

    // Exécuter la requête
    if ($stmt->execute()) {
        header("Location: mes_favoris.php");
        exit();
    } else {
        die("Erreur lors de l'ajout du livre à vos favoris.");
    }
} else {
    die("Erreur : Données du formulaire manquantes.");
}
?>
