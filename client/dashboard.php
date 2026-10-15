<?php
require_once '../config.php';

if (!estConnecte() || !isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'client') {
    rediriger('login.php');
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$user_id]);
    $client = $stmt->fetch();
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM idees WHERE client_id = ?");
    $stmt->execute([$user_id]);
    $idees_count = $stmt->fetch()['count'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM projets WHERE client_id = ?");
    $stmt->execute([$user_id]);
    $projets_count = $stmt->fetch()['count'];
    
    $stmt = $conn->prepare("SELECT * FROM idees WHERE client_id = ? ORDER BY id DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $idees = $stmt->fetchAll();
    
    $stmt = $conn->prepare("SELECT p.*, f.nom as freelancer_nom FROM projets p JOIN freelancers f ON p.freelancer_id = f.id WHERE p.client_id = ? ORDER BY p.id DESC LIMIT 5");
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
    <title>Tableau de bord Client - FreelanceHub</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="../index.php" class="logo">FreelanceHub</a>
                <div class="nav-menu">
                    <a href="dashboard.php">Tableau de bord</a>
                    <a href="ajouter_idee.php">Ajouter une idée</a>
                    <a href="suivi_projets.php">Suivi projets</a>
                    <a href="voir_projet_realiser.php">Projets realiser</a>
                    <a href="mes_propositions.php">Mes propositions</a>
                    <a href="reclamation.php" style="color: #0a020f;">
                    <i class="fas fa-exclamation-triangle"></i> Réclamation </a>
                    <a href="logout.php" class="btn btn-outline">Déconnexion</a>
                </div>
            </nav>
        </div>
    </header>

    <main class="container" style="margin-top: 80px;">
        <h1>Bonjour, <?php echo $client['nom']; ?></h1>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3><?php echo $idees_count; ?></h3>
                <p>Mes idées</p> <br>
                <a href="ajouter_idee.php" class="btn btn-primary">Ajouter</a>
            </div>
            
            <div class="stat-card">
                <h3><?php echo $projets_count; ?></h3>
                                <p>Mes projets</p><br>
                <a href="suivi_projets.php" class="btn btn-primary">Voir</a>
            </div>
        </div>
        
        <h2 style="margin: 40px 0 20px 0;">Mes idées récentes</h2>
        
        <?php if (count($idees) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Description</th>
                            <th>Budget</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($idees as $idee): ?>
                        <tr>
                            <td><?php echo $idee['titre']; ?></td>
                            <td><?php echo substr($idee['description'], 0, 100) . '...'; ?></td>
                            <td><?php echo $idee['budget']; ?> MAD</td>
                            <td><?php echo $idee['statut']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>Vous n'avez pas encore d'idées.</p>
            <a href="ajouter_idee.php" class="btn btn-primary">Ajouter une idée</a>
        <?php endif; ?>
        
        <h2 style="margin: 40px 0 20px 0;">Mes projets en cours</h2>
        
        <?php if (count($projets) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Freelancer</th>
                            <th>Prix</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projets as $projet): ?>
                        <tr>
                            <td><?php echo $projet['titre']; ?></td>
                            <td><?php echo $projet['freelancer_nom']; ?></td>
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
    </main>
    
    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 FreelanceHub - Espace Client</p>
        </div>
    </footer>
</body>
</html>