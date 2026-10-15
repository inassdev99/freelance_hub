<?php
require_once '../config.php';

if (!estConnecte() || !isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'client') {
    rediriger('login.php');
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("SELECT p.*, f.nom as freelancer_nom FROM projets p JOIN freelancers f ON p.freelancer_id = f.id WHERE p.client_id = ? ORDER BY p.id DESC");
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
    <title>Suivi des projets - FreelanceHub</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="../index.php" class="logo">FreelanceHub</a>
                <div class="nav-menu">
                    <a href="dashboard.php"> Tableau de bord</a>
                    <a href="suivi_projets.php">Suivi projets</a>
                </div>
            </nav>
        </div>
    </header>

    <main class="container" style="margin-top: 80px;">
        <h1>Suivi de mes projets</h1>
        
        <?php if (count($projets) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Freelancer</th>
                            <th>Description</th>
                            <th>Prix</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projets as $projet): ?>
                        <tr>
                            <td><?php echo $projet['titre']; ?></td>
                            <td><?php echo $projet['freelancer_nom']; ?></td>
                            <td><?php echo substr($projet['description'], 0, 100) . '...'; ?></td>
                            <td><?php echo $projet['prix']; ?> MAD</td>
                            <td>
                                <?php
                                $status_colors = [
                                    'en_cours' => '#fff3cd',
                                    'termine' => '#d4edda',
                                    'pret_a_vendre' => '#d1ecf1'
                                ];
                                ?>
                                <span style="
                                    padding: 5px 10px;
                                    border-radius: 20px;
                                    font-size: 12px;
                                    background: <?php echo $status_colors[$projet['statut']] ?? '#f8f9fa'; ?>;
                                ">
                                    <?php echo $projet['statut']; ?>
                                </span>
                            </td>
                            <td>
                                <a href="details_projet.php?id=<?php echo $projet['id']; ?>" class="btn btn-outline">
                                    Voir détails
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="background: #f8f9fa; padding: 40px; text-align: center; border-radius: 10px; margin: 40px 0;">
                <p style="font-size: 18px; margin-bottom: 20px;">Vous n'avez pas encore de projets en cours.</p>
                <a href="ajouter_idee.php" class="btn btn-primary">Ajouter une idée de projet</a>
            </div>
        <?php endif; ?>
        
        <p style="margin-top: 20px;">
            <a href="dashboard.php" class="btn btn-outline">← Retour au tableau de bord</a>
        </p>
    </main>
</body>
</html>