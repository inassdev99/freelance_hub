<?php
require_once '../config.php';

if (!estConnecte() || !isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
    rediriger('login.php');
}

$action = isset($_GET['action']) ? securiser($_GET['action']) : '';
$table = isset($_GET['table']) ? securiser($_GET['table']) : '';

// Actions de suppression
if ($action == 'delete' && !empty($table)) {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id > 0) {
        try {
            $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Suppression réussie";
        } catch(PDOException $e) {
            $error = "Erreur lors de la suppression : " . $e->getMessage();
        }
    }
}

try {
    // Récupérer toutes les données
    $freelancers = $conn->query("SELECT * FROM freelancers ORDER BY id DESC")->fetchAll();
    $clients = $conn->query("SELECT * FROM clients ORDER BY id DESC")->fetchAll();
    $idees = $conn->query("SELECT i.*, c.nom as client_nom FROM idees i JOIN clients c ON i.client_id = c.id ORDER BY i.id DESC")->fetchAll();
    $projets = $conn->query("SELECT p.*, f.nom as freelancer_nom, c.nom as client_nom FROM projets p JOIN freelancers f ON p.freelancer_id = f.id JOIN clients c ON p.client_id = c.id ORDER BY p.id DESC")->fetchAll();
    
    // AJOUT: Récupérer les projets réalisés
    $projets_realises = $conn->query("SELECT pr.*, f.nom as freelancer_nom FROM projets_realises pr JOIN freelancers f ON pr.freelancer_id = f.id ORDER BY pr.date_ajout DESC")->fetchAll();
    
} catch(PDOException $e) {
    // Si la table projets_realises n'existe pas initialiser un tableau vide
    $projets_realises = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Admin - FreelanceHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .tabs {
            display: flex;
            gap: 10px;
            margin: 30px 0;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            flex-wrap: wrap;
        }
        
        .tab {
            padding: 10px 20px;
            background: #f8f9fa;
            border: none;
            border-radius: 5px 5px 0 0;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .tab:hover {
            background: #e9ecef;
        }
        
        .tab.active {
            background: #4361ee;
            color: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            border: none;
            cursor: pointer;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #333;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            border: none;
            cursor: pointer;
        }
        
        /* style pour le badge catégorie */
        .badge-categorie {
            background: #9d4edd;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        /* style pour le prix dans projets réalisés */
        .price-realise {
            font-weight: bold;
            color: #28a745;
        }
        
        /* style pour l'onglet projets réalisés */
        .tab-realises {
            background: #f8f9fa;
        }
        
        .tab-realises.active {
            background: #9d4edd;
            color: white;
        }
        
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        @media (max-width: 768px) {
            .tabs {
                flex-direction: column;
            }
            
            .tab {
                width: 100%;
                border-radius: 5px;
                margin-bottom: 5px;
            }
            
            .table-container {
                font-size: 14px;
            }
            
            th, td {
                padding: 8px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <header class="header" style="background: #333;">
        <div class="container">
            <nav class="navbar">
                <a href="../index.php" class="logo" style="color: white;">FreelanceHub <span style="color: #4361ee;">Admin</span></a>
                <div class="nav-menu">
                    <a href="dashboard.php" style="color: white;">Tableau de bord</a>
                    <a href="gestion.php" style="color: white; font-weight: bold;">Gestion</a>
                </div>
            </nav>
        </div>
    </header>

    <main class="container" style="margin-top: 80px;">
        <h1>Gestion de la plateforme</h1>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 15px 0;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 15px 0;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <!-- Tabs onglet Projets Réalisés -->
        <div class="tabs">
            <button class="tab active" onclick="showTab('freelancers')">Freelancers</button>
            <button class="tab" onclick="showTab('clients')">Clients</button>
            <button class="tab" onclick="showTab('idees')">Idées</button>
            <button class="tab" onclick="showTab('projets')">Projets</button>
            <button class="tab tab-realises active" onclick="showTab('projets_realises')">Projets Réalisés</button>
        </div>
        
        <!-- Tab Contents -->
        <div id="freelancers" class="tab-content active">
            <h2>Gestion des Freelancers</h2>
            <?php if (count($freelancers) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($freelancers as $freelancer): ?>
                            <tr>
                                <td><?php echo $freelancer['id']; ?></td>
                                <td><?php echo $freelancer['nom']; ?></td>
                                <td><?php echo $freelancer['email']; ?></td>
                                <td class="action-buttons">
                                    <a href="?action=delete&table=freelancers&id=<?php echo $freelancer['id']; ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Supprimer ce freelancer ?')">
                                        Supprimer
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>Aucun freelancer inscrit.</p>
            <?php endif; ?>
        </div>
        
        <div id="clients" class="tab-content">
            <h2>Gestion des Clients</h2>
            <?php if (count($clients) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><?php echo $client['id']; ?></td>
                                <td><?php echo $client['nom']; ?></td>
                                <td><?php echo $client['email']; ?></td>
                                <td class="action-buttons">
                                    <a href="?action=delete&table=clients&id=<?php echo $client['id']; ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Supprimer ce client ?')">
                                        Supprimer
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>Aucun client inscrit.</p>
            <?php endif; ?>
        </div>
        
        <div id="idees" class="tab-content">
            <h2>Gestion des Idées</h2>
            <?php if (count($idees) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Client</th>
                                <th>Budget</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($idees as $idee): ?>
                            <tr>
                                <td><?php echo $idee['id']; ?></td>
                                <td><?php echo $idee['titre']; ?></td>
                                <td><?php echo $idee['client_nom']; ?></td>
                                <td><?php echo $idee['budget']; ?> €</td>
                                <td>
                                    <span style="background: #f0f5ff; color: #4361ee; padding: 3px 8px; border-radius: 12px; font-size: 12px;">
                                        <?php echo $idee['statut']; ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <a href="?action=delete&table=idees&id=<?php echo $idee['id']; ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Supprimer cette idée ?')">
                                        Supprimer
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>Aucune idée publiée.</p>
            <?php endif; ?>
        </div>
        
        <div id="projets" class="tab-content">
            <h2>Gestion des Projets</h2>
            <?php if (count($projets) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Freelancer</th>
                                <th>Client</th>
                                <th>Prix</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projets as $projet): ?>
                            <tr>
                                <td><?php echo $projet['id']; ?></td>
                                <td><?php echo $projet['titre']; ?></td>
                                <td><?php echo $projet['freelancer_nom']; ?></td>
                                <td><?php echo $projet['client_nom']; ?></td>
                                <td><?php echo $projet['prix']; ?> €</td>
                                <td>
                                    <span style="background: #f0f5ff; color: #4361ee; padding: 3px 8px; border-radius: 12px; font-size: 12px;">
                                        <?php echo $projet['statut']; ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <a href="?action=delete&table=projets&id=<?php echo $projet['id']; ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Supprimer ce projet ?')">
                                        Supprimer
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>Aucun projet en cours.</p>
            <?php endif; ?>
        </div>
        
        <!-- AJOUT: Section Projets Réalisés -->
        <div id="projets_realises" class="tab-content">
            <h2>Gestion des Projets Réalisés</h2>
            <?php if (count($projets_realises) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Freelancer</th>
                                <th>Catégorie</th>
                                <th>Prix</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projets_realises as $projet): ?>
                            <tr>
                                <td><?php echo $projet['id']; ?></td>
                                <td><?php echo $projet['titre']; ?></td>
                                <td><?php echo $projet['freelancer_nom']; ?></td>
                                <td>
                                    <span class="badge-categorie">
                                        <?php echo $projet['categorie']; ?>
                                    </span>
                                </td>
                                <td class="price-realise">
                                    <?php echo number_format($projet['prix'], 2, ',', ' '); ?> MAD
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($projet['date_ajout'])); ?></td>
                                <td class="action-buttons">
                                    <a href="?action=delete&table=projets_realises&id=<?php echo $projet['id']; ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Supprimer ce projet réalisé ?')">
                                        Supprimer
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #666;">
                    <i class="fas fa-trophy" style="font-size: 50px; color: #ddd; margin-bottom: 15px;"></i>
                    <p>Aucun projet réalisé dans la base de données.</p>
                    <p style="font-size: 14px; color: #999;">
                        Les freelancers peuvent ajouter leurs projets réalisés dans leur espace personnel.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 FreelanceHub - Administration</p>
        </div>
    </footer>
    
    <script>
        function showTab(tabName) {
            // Masquer tous les contenus
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Désactiver tous les onglets
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Afficher le contenu sélectionné
            document.getElementById(tabName).classList.add('active');
            
            // Activer l'onglet sélectionné
            event.target.classList.add('active');
        }
        
        // Initialiser avec le bon onglet actif
        document.addEventListener('DOMContentLoaded', function() {
            // Par défaut, premier onglet actif
            const firstTab = document.querySelector('.tab');
            if (firstTab) {
                firstTab.classList.add('active');
            }
        });
    </script>
</body>
</html>