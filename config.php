<?php
// config.php
session_start();

$host = "localhost";
$dbname = "freelancehub";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

function securiser($data) {
    return trim($data);
}

function estConnecte() {
    return isset($_SESSION['user_id']);
}

function rediriger($page) {
    header("Location: $page");
    exit();
}
?>