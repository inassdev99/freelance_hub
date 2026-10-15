<?php
require_once '../config.php';

if (!estConnecte() || !isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
    rediriger('login.php');
}

try {
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM freelancers");
    $freelancers_count = $stmt->fetch()['count'];
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM clients");
    $clients_count = $stmt->fetch()['count'];
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM idees");
    $idees_count = $stmt->fetch()['count'];
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM projets");
    $projets_count = $stmt->fetch()['count'];
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM projets_realises");
    $projets_realises_count = $stmt->fetch()['count'];
    $stmt = $conn->query("SELECT * FROM freelancers ORDER BY id DESC LIMIT 5");
    $freelancers = $stmt->fetchAll();
    
    $stmt = $conn->query("SELECT * FROM clients ORDER BY id DESC LIMIT 5");
    $clients = $stmt->fetchAll();
    
    $stmt = $conn->query("SELECT i.*, c.nom as client_nom FROM idees i JOIN clients c ON i.client_id = c.id ORDER BY i.id DESC LIMIT 5");
    $idees = $stmt->fetchAll();
    
    $stmt = $conn->query("SELECT p.*, f.nom as freelancer_nom, c.nom as client_nom FROM projets p JOIN freelancers f ON p.freelancer_id = f.id JOIN clients c ON p.client_id = c.id ORDER BY p.id DESC LIMIT 5");
    $projets = $stmt->fetchAll();
    $stmt = $conn->query("SELECT pr.*, f.nom as freelancer_nom FROM projets_realises pr JOIN freelancers f ON pr.freelancer_id = f.id ORDER BY pr.date_ajout DESC LIMIT 5");
    $projets_realises = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $projets_realises_count = 0;
    $projets_realises = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Admin - FreelanceHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* styles  pour l'admin */
        .admin-header {
            background: #333;
            color: white;
        }
        
        .admin-header .nav-menu a {
            color: white !important;
        }
        
        .admin-header .btn-outline {
            color: white !important;
            border-color: white !important;
        }
        
        .admin-header .btn-outline:hover {
            background: white !important;
            color: #333 !important;
        }
        .admin-stats {
            display: grid;
            grid-template-columns: repeat(5, 1fr); 
            gap: 15px;
            margin: 40px 0;
        }
        
        .admin-stat-card {
            background: white;
            padding: 25px 15px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            border: none;
            min-height: 140px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .admin-stat-card:hover {
            transform: translateY(-5px);
        }
        
        .admin-stat-card h3 {
            font-size: 36px;
            margin: 10px 0;
            font-weight: 700;
        }
        
        .admin-stat-card p {
            font-size: 16px;
            color: #666;
            font-weight: 500;
            margin: 0;
        }
        
        /* les couleur pour les bouton */
        .stat-card-freelancers {
            border-top: 5px solid #4361ee;
        }
        
        .stat-card-clients {
            border-top: 5px solid #7209b7;
        }
        
        .stat-card-idees {
            border-top: 5px solid #4cc9f0;
        }
        
        .stat-card-projets {
            border-top: 5px solid #f72585;
        }
        
        .stat-card-realises {
            border-top: 5px solid #9d4edd;
        }
        
        /* section container */
        .section-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin: 35px 0;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: 1px solid #09141f;
        }
        
        .section-container h2 {
            color: #333;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #10161d;
        }
        
        /* Amelioration des tables */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .admin-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #12181f;
        }
        
        .admin-table td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            color: #555;
        }
        
        .admin-table tr:hover {
            background: #f8f9fa;
        }
        
        /* Badges pour statuts */
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-en_attente {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-accepte {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-refuse {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge-en_cours {
            background: #cce5ff;
            color: #004085;
        }
        
        .badge-termine {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .badge-pret_a_vendre {
            background: #d4edda;
            color: #155724;
        }
        
        /* AJOUT: badge pour les projets réalisés */
        .badge-portfolio {
            background: #e0cffc;
            color: #5a189a;
        }
        
        /* AJOUT: colonne prix dans table */
        .price-column {
            font-weight: bold;
            color: #28a745;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .admin-stats {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .admin-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .admin-stat-card h3 {
                font-size: 32px;
            }
            
            .admin-table {
                display: block;
                overflow-x: auto;
            }
        }
        
        @media (max-width: 480px) {
            .admin-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header admin-header">
        <div class="container">
            <nav class="navbar">
                <a href="../index.php" class="logo" style="color: white;">
                    <i class="fas fa-cog"></i> FreelanceHub <span style="color: #4cc9f0;">Admin</span>
                </a>
                <div class="nav-menu">
                    <a href="dashboard.php" style="color: white; font-weight: 600;">
                        <i class="fas fa-tachometer-alt"></i> Tableau de bord
                    </a>
                    <a href="gestion.php" style="color: white;">
                        <i class="fas fa-tasks"></i> Gestion
                    </a>
                    <a href="reclamations.php" style="position: relative;">
                        <i class="fas fa-exclamation-triangle"></i> Réclamations </a>
                    <a href="logout.php" class="btn btn-outline">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <main class="container" style="margin-top: 100px;">
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
            <div style="width: 50px; height: 50px; background: #4361ee; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                <i class="fas fa-user-shield"></i>
            </div>
            <div>
                <h1 style="margin: 0; color: #130f0f;">Tableau de bord Administrateur</h1>
                <p style="color: #2e2323; margin: 5px 0 0 0;">Gestion complète de la plateforme</p>
            </div>
        </div>
        
        <!-- MODIFICATION: 5 boutons alignés de même taille -->
        <div class="admin-stats">
            <!-- Bouton Freelancers -->
            <div class="admin-stat-card stat-card-freelancers">
                <div style="font-size: 28px; color: #4361ee;">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3 style="color: #4361ee;"><?php echo $freelancers_count; ?></h3>
                <p>Freelancers</p>
            </div>
            
            <!-- Bouton Clients -->
            <div class="admin-stat-card stat-card-clients">
                <div style="font-size: 28px; color: #7209b7;">
                    <i class="fas fa-briefcase"></i>
                </div>
                <h3 style="color: #7209b7;"><?php echo $clients_count; ?></h3>
                <p>Clients</p>
            </div>
            
            <!-- Bouton Idées -->
            <div class="admin-stat-card stat-card-idees">
                <div style="font-size: 28px; color: #4cc9f0;">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <h3 style="color: #4cc9f0;"><?php echo $idees_count; ?></h3>
                <p>Idées</p>
            </div>
            
            <!-- Bouton Projets -->
            <div class="admin-stat-card stat-card-projets">
                <div style="font-size: 28px; color: #f72585;">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <h3 style="color: #f72585;"><?php echo $projets_count; ?></h3>
                <p>Projets</p>
            </div>
            
            <!-- Bouton Projets Réalisés -->
            <div class="admin-stat-card stat-card-realises">
                <div style="font-size: 28px; color: #9d4edd;">
                    <i class="fas fa-trophy"></i>
                </div>
                <h3 style="color: #9d4edd;"><?php echo $projets_realises_count; ?></h3>
                <p>Projets Réalisés</p>
            </div>
        </div>
        
        <!-- Freelancers récents -->
        <div class="section-container">
            <h2><i class="fas fa-user-tie"></i> Freelancers récents</h2>
            <?php if (count($freelancers) > 0): ?>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($freelancers as $freelancer): ?>
                            <tr>
                                <td><strong>#<?php echo $freelancer['id']; ?></strong></td>
                                <td><?php echo $freelancer['nom']; ?></td>
                                <td><?php echo $freelancer['email']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #666;">
                    <i class="fas fa-user-tie" style="font-size: 50px; color: #ddd; margin-bottom: 15px;"></i>
                    <p>Aucun freelancer inscrit.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Clients récents -->
        <div class="section-container">
            <h2><i class="fas fa-briefcase"></i> Clients récents</h2>
            <?php if (count($clients) > 0): ?>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><strong>#<?php echo $client['id']; ?></strong></td>
                                <td><?php echo $client['nom']; ?></td>
                                <td><?php echo $client['email']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #666;">
                    <i class="fas fa-briefcase" style="font-size: 50px; color: #ddd; margin-bottom: 15px;"></i>
                    <p>Aucun client inscrit.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Idées récentes -->
        <div class="section-container">
            <h2><i class="fas fa-lightbulb"></i> Idées récentes</h2>
            <?php if (count($idees) > 0): ?>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Client</th>
                                <th>Budget</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($idees as $idee): ?>
                            <tr>
                                <td><strong>#<?php echo $idee['id']; ?></strong></td>
                                <td><?php echo $idee['titre']; ?></td>
                                <td><?php echo $idee['client_nom']; ?></td>
                                <td><?php echo $idee['budget'] ? $idee['budget'] . ' €' : 'Non spécifié'; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $idee['statut']; ?>">
                                        <?php echo $idee['statut']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #666;">
                    <i class="fas fa-lightbulb" style="font-size: 50px; color: #ddd; margin-bottom: 15px;"></i>
                    <p>Aucune idée publiée.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Projets récents -->
        <div class="section-container">
            <h2><i class="fas fa-project-diagram"></i> Projets récents</h2>
            <?php if (count($projets) > 0): ?>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Freelancer</th>
                                <th>Client</th>
                                <th>Prix</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projets as $projet): ?>
                            <tr>
                                <td><strong>#<?php echo $projet['id']; ?></strong></td>
                                <td><?php echo $projet['titre']; ?></td>
                                <td><?php echo $projet['freelancer_nom']; ?></td>
                                <td><?php echo $projet['client_nom']; ?></td>
                                <td><?php echo $projet['prix']; ?> €</td>
                                <td>
                                    <span class="badge badge-<?php echo $projet['statut']; ?>">
                                        <?php echo $projet['statut']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #666;">
                    <i class="fas fa-project-diagram" style="font-size: 50px; color: #ddd; margin-bottom: 15px;"></i>
                    <p>Aucun projet en cours.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- AJOUT: Projets réalisés récents -->
        <div class="section-container">
            <h2><i class="fas fa-trophy"></i> Projets réalisés récents</h2>
            <?php if (count($projets_realises) > 0): ?>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Freelancer</th>
                                <th>Catégorie</th>
                                <th>Prix</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projets_realises as $projet): ?>
                            <tr>
                                <td><strong>#<?php echo $projet['id']; ?></strong></td>
                                <td><?php echo $projet['titre']; ?></td>
                                <td><?php echo $projet['freelancer_nom']; ?></td>
                                <td>
                                    <span class="badge badge-portfolio">
                                        <?php echo $projet['categorie']; ?>
                                    </span>
                                </td>
                                <td class="price-column">
                                    <?php echo number_format($projet['prix'], 2, ',', ' '); ?> MAD
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($projet['date_ajout'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #666;">
                    <i class="fas fa-trophy" style="font-size: 50px; color: #ddd; margin-bottom: 15px;"></i>
                    <p>Aucun projet réalisé ajouté au portfolio.</p>
                    <p style="font-size: 14px; color: #999;">
                        Les freelancers peuvent ajouter leurs projets réalisés dans leur espace.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <footer class="footer" style="margin-top: 80px;">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <p>&copy; 2026 FreelanceHub - Administration</p>
                <p style="color: #666; font-size: 14px;">
                    <i class="fas fa-user-shield"></i> Connecté en tant qu'administrateur
                </p>
            </div>
        </div>
    </footer>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statNumbers = document.querySelectorAll('.admin-stat-card h3');
            
            statNumbers.forEach(stat => {
                const originalText = stat.textContent;
                const finalNumber = parseInt(originalText);
                
                if (!isNaN(finalNumber)) {
                    let current = 0;
                    const increment = finalNumber / 50;
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= finalNumber) {
                            current = finalNumber;
                            clearInterval(timer);
                        }
                        stat.textContent = Math.floor(current);
                    }, 30);
                }
            });
        });
    </script>
</body>
</html>