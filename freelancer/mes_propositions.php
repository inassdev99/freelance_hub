<?php
require_once '../config.php';

if (!estConnecte() || !isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'freelancer') {
    rediriger('login.php');
}

$user_id = $_SESSION['user_id'];

try {
    // Récupérer les propositions du freelancer
    $stmt = $conn->prepare("SELECT p.*, i.titre as idee_titre, i.budget as budget_client,
                           c.nom as client_nom, c.email as client_email,
                           CASE 
                               WHEN p.statut = 'en_attente' THEN '⏳ En attente'
                               WHEN p.statut = 'accepte' THEN '✅ Acceptée'
                               WHEN p.statut = 'refuse' THEN '❌ Refusée'
                           END as statut_texte
                           FROM propositions p
                           JOIN idees i ON p.idee_id = i.id
                           JOIN clients c ON i.client_id = c.id
                           WHERE p.freelancer_id = ?
                           ORDER BY p.date_proposition DESC");
    $stmt->execute([$user_id]);
    $propositions = $stmt->fetchAll();
    
} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes propositions - FreelanceHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Styles supplémentaires spécifiques pour cette page */
        .nav-menu a.active {
            color: #4361ee;
            font-weight: bold;
        }
        
        main.container {
            margin-top: 80px;
        }
        
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        
        .status-accepted {
            color: #28a745;
            font-weight: bold;
        }
        
        .status-rejected {
            color: #dc3545;
            font-weight: bold;
        }
        
        .back-link {
            margin-top: 30px;
            display: inline-block;
        }
        
        .price-cell {
            font-weight: bold;
            color: #4361ee;
        }
        
        .no-data {
            background: white;
            padding: 60px 20px;
            border-radius: 10px;
            text-align: center;
            color: #666;
            margin: 30px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .no-data-icon {
            font-size: 60px;
            color: #ddd;
            margin-bottom: 20px;
            display: block;
        }
        
        .no-data h3 {
            margin-bottom: 10px;
            color: #333;
        }
        
        h1 {
            margin-bottom: 30px;
            color: #333;
        }
        
        .btn {
            display: inline-block;
            text-decoration: none;
            text-align: center;
        }
        
        .stat-card {
            margin: 20px 0;
        }
        
        .stat-card .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #4361ee;
            display: block;
        }
        
        .stat-card .stat-label {
            color: #666;
            font-size: 16px;
        }
        
        .stats-header {
            margin-bottom: 30px;
        }
        
        .stats-header h2 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .stats-header p {
            color: #666;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="../index.php" class="logo">FreelanceHub</a>
                <div class="nav-menu">
                    <a href="dashboard.php">Tableau de bord</a>
                    <a href="mes_propositions.php">Mes propositions</a>
                   
                </div>
            </nav>
        </div>
    </header>

    <main class="container">
        <h1>Mes propositions envoyées</h1>
        
        <!-- Statistiques -->
        <div class="dashboard-stats stats-header">
            <div class="stat-card">
                <span class="stat-number"><?php echo count($propositions); ?></span>
                <span class="stat-label">Propositions envoyées</span>
            </div>
            <div class="stat-card">
                <span class="stat-number">
                    <?php 
                        $accepted = array_filter($propositions, function($p) {
                            return $p['statut'] == 'accepte';
                        });
                        echo count($accepted);
                    ?>
                </span>
                <span class="stat-label">Propositions acceptées</span>
            </div>
        </div>
        
        <?php if (count($propositions) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Projet</th>
                            <th>Client</th>
                            <th>Budget client</th>
                            <th>Ma proposition</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($propositions as $proposition): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($proposition['idee_titre']); ?></td>
                            <td><?php echo htmlspecialchars($proposition['client_nom']); ?></td>
                            <td><?php echo $proposition['budget_client'] ? htmlspecialchars($proposition['budget_client']) . ' MAD' : 'Non spécifié'; ?></td>
                            <td class="price-cell"><?php echo htmlspecialchars($proposition['prix_propose']); ?> MAD</td>
                            <td>
                                <?php if ($proposition['statut'] == 'en_attente'): ?>
                                    <span class="status-pending">⏳ En attente</span>
                                <?php elseif ($proposition['statut'] == 'accepte'): ?>
                                    <span class="status-accepted">✅ Acceptée</span>
                                <?php else: ?>
                                    <span class="status-rejected">❌ Refusée</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($proposition['date_proposition'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-data">
                <span class="no-data-icon">📝</span>
                <h3>Aucune proposition envoyée</h3>
                <p>Vous n'avez pas encore postulé à des idées.</p><br>
                <a href="idees.php" class="btn btn-primary">Voir les idées disponibles</a>
            </div>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="dashboard.php" class="btn btn-outline">← Retour au tableau de bord</a>
        </div>
    </main>
</body>
</html>