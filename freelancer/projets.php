<?php
require_once '../config.php';

if (!estConnecte() || !isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'freelancer') {
    rediriger('login.php');
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("SELECT p.*, c.nom as client_nom FROM projets p JOIN clients c ON p.client_id = c.id WHERE p.freelancer_id = ? ORDER BY p.id DESC");
    $stmt->execute([$user_id]);
    $projets = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes projets - FreelanceHub</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="../index.php" class="logo">FreelanceHub</a>
                <div class="nav-menu">
                    <a href="dashboard.php">Tableau de bord</a>
                    <a href="projets.php">Projets</a>
                    <a href="projet_realiser.php">Projets realiser</a>
                </div>
            </nav>
        </div>
    </header>

    <main class="container" style="margin-top: 80px;">
        <h1>Mes projets</h1>
        
        <?php if (count($projets) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Description</th>
                            <th>Client</th>
                            <th>Prix</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projets as $projet): ?>
                        <tr>
                            <td><?php echo $projet['titre']; ?></td>
                            <td><?php echo $projet['description']; ?></td>
                            <td><?php echo $projet['client_nom']; ?></td>
                            <td><?php echo $projet['prix']; ?> MAD</td>
                            <td><?php echo $projet['statut']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>Vous n'avez pas encore de projets.</p>
        <?php endif; ?>
        
        <p style="margin-top: 20px;">
            <a href="dashboard.php" class="btn btn-outline">Retour au tableau de bord</a>
        </p>
    </main>
</body>
</html>