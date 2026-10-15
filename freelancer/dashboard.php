<?php
require_once '../config.php';

if (!estConnecte() || !isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'freelancer') {
    rediriger('login.php');
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("SELECT * FROM freelancers WHERE id = ?");
    $stmt->execute([$user_id]);
    $freelancer = $stmt->fetch();
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM idees WHERE statut = 'en_attente'");
    $stmt->execute();
    $idees_count = $stmt->fetch()['count'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM projets WHERE freelancer_id = ?");
    $stmt->execute([$user_id]);
    $projets_count = $stmt->fetch()['count'];
    
    $stmt = $conn->prepare("SELECT i.*, c.nom as client_nom FROM idees i JOIN clients c ON i.client_id = c.id WHERE i.statut = 'en_attente' ORDER BY i.id DESC LIMIT 5");
    $stmt->execute();
    $idees = $stmt->fetchAll();
    
    $stmt = $conn->prepare("SELECT p.*, c.nom as client_nom FROM projets p JOIN clients c ON p.client_id = c.id WHERE p.freelancer_id = ? ORDER BY p.id DESC LIMIT 5");
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
    <title>Tableau de bord Freelancer - FreelanceHub</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="../index.php" class="logo">FreelanceHub</a>
                <div class="nav-menu">
                    <a href="dashboard.php">Tableau de bord</a>
                    <a href="idees.php">Idées</a>
                    <a href="projets.php">Projets</a>
                    <a href="mes_propositions.php">Mes propositions</a>
                    <a href="reclamation.php">
                    <i class="fas fa-exclamation-triangle"></i> Réclamation</a>
                    <a href="logout.php" class="btn btn-outline">Déconnexion</a>
                </div>
            </nav>
        </div>
    </header>

    <main class="container" style="margin-top: 70px;">
        <h1>Bonjour, <?php echo $freelancer['nom']; ?></h1>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3><?php echo $idees_count; ?></h3>
                <p>Idées disponibles</p> <br>
                <a href="idees.php" class="btn btn-primary">Voir</a>
            </div>
            
            <div class="stat-card">
                <h3><?php echo $projets_count; ?></h3>
                <p>Mes projets</p> <br>
                <a href="projets.php" class="btn btn-primary">Voir</a>
            </div>
        </div>
        
        <h2 style="margin: 40px 0 20px 0;">Dernières idées disponibles</h2>
        
        <?php if (count($idees) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Client</th>
                            <th>Budget</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($idees as $idee): ?>
                        <tr>
                            <td><?php echo $idee['titre']; ?></td>
                            <td><?php echo $idee['client_nom']; ?></td>
                            <td><?php echo $idee['budget']; ?> MAD</td>
                            <td><?php echo $idee['statut']; ?></td>
                            <td>
                                <a href="postuler.php?id=<?php echo $idee['id']; ?>" class="btn btn-primary">
                                    Postuler
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>Aucune idée disponible</p>
        <?php endif; ?>
        
        <h2 style="margin: 40px 0 20px 0;">Mes projets</h2>
        
        <?php if (count($projets) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Client</th>
                            <th>Prix</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projets as $projet): ?>
                        <tr>
                            <td><?php echo $projet['titre']; ?></td>
                            <td><?php echo $projet['client_nom']; ?></td>
                            <td><?php echo $projet['prix']; ?> MAD</td>
                            <td><?php echo $projet['statut']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>Vous n'avez pas encore de projets</p>
        <?php endif; ?>
    </main>
    
    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 FreelanceHub - Espace Freelancer</p>
        </div>
    </footer>
</body>
</html>