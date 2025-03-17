<?php
session_start();
require 'database.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['favori_id'])) {
    header("Location: mes_favoris.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();

$stmt = $conn->prepare("DELETE FROM Favoris WHERE id = ? AND user_id = ?");
$stmt->execute([$_POST['favori_id'], $_SESSION['user_id']]);

header("Location: mes_favoris.php");
?>
