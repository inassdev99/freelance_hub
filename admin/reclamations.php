<?php
require_once '../config.php';

// Vérifier si admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Récupérer toutes les réclamations
$sql = "SELECT r.*, 
               CASE 
                   WHEN r.user_type = 'freelancer' THEN f.nom
                   WHEN r.user_type = 'client' THEN c.nom
               END as nom_utilisateur
        FROM reclamations r
        LEFT JOIN freelancers f ON r.user_id = f.id AND r.user_type = 'freelancer'
        LEFT JOIN clients c ON r.user_id = c.id AND r.user_type = 'client'
        ORDER BY r.date_creation DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$reclamations = $stmt->fetchAll();

// Statistiques
$ouvertes = 0;
$en_cours = 0;
$resolues = 0;

foreach ($reclamations as $rec) {
    if ($rec['statut'] == 'ouverte') $ouvertes++;
    if ($rec['statut'] == 'en_cours') $en_cours++;
    if ($rec['statut'] == 'resolue') $resolues++;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Réclamations - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .container { max-width: 1200px; margin: auto; padding: 20px; }
        
        /* Menu */
        .menu-admin {
            background: #2c333a;
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .menu-content {
            max-width: 1200px;
            margin: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        .admin-brand {
            font-size: 20px;
            font-weight: bold;
            color: white;
        }
        .menu-links {
            display: flex;
            gap: 10px;
        }
        .menu-admin a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
            transition: background 0.3s;
        }
        .menu-admin a:hover {
            background: rgba(80, 125, 192, 0.1);
        }
        
        /* Statistiques */
        .stats {
            display: flex;
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .stat-card h3 {
            font-size: 32px;
            margin: 10px 0;
        }
        
        /* Table */
        .table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: bold;
            color: #4a79a8;
            border-bottom: 2px solid #dee2e6;
        }
        td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        tr:hover {
            background: #f8f9fa;
        }
        
        /* Badges */
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-ouverte { background: #fff3cd; color: #856404; }
        .badge-en_cours { background: #cce5ff; color: #004085; }
        .badge-resolue { background: #d4edda; color: #155724; }
        
        /* Boutons */
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        .btn-view {
            background: #007bff;
            color: white;
        }
        .btn-view:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    
    <!-- Menu Admin -->
    <div class="menu-admin">
        <div class="menu-content">
            <div class="admin-brand">FreelanceHub Admin</div>
            <div class="menu-links">
                <a href="dashboard.php">Tableau de bord</a>
                <a href="reclamations.php">Reclamation</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <h1 style="color: #2c3e50;"> Les réclamations</h1>
        
        <!-- Statistiques -->
        <div class="stats">
            <div class="stat-card" style="border-top: 4px solid #ffc107;">
                <h3 style="color: #ffc107;"><?php echo $ouvertes; ?></h3>
                <p>Ouvertes</p>
            </div>
            <div class="stat-card" style="border-top: 4px solid #007bff;">
                <h3 style="color: #007bff;"><?php echo $en_cours; ?></h3>
                <p>En cours</p>
            </div>
            <div class="stat-card" style="border-top: 4px solid #28a745;">
                <h3 style="color: #28a745;"><?php echo $resolues; ?></h3>
                <p>Résolues</p>
            </div>
            <div class="stat-card" style="border-top: 4px solid #6c757d;">
                <h3 style="color: #6c757d;"><?php echo count($reclamations); ?></h3>
                <p>Total</p>
            </div>
        </div>
        
        <!-- Table des réclamations -->
        <div class="table-container">
            <?php if(count($reclamations) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Utilisateur</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reclamations as $rec): ?>
                        <tr>
                            <td><strong>#<?php echo $rec['id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($rec['titre']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($rec['nom_utilisateur'] ?? 'Utilisateur'); ?>
                                <br><small style="color: #6c757d;">(<?php echo $rec['user_type']; ?>)</small>
                            </td>
                            <td><?php echo ucfirst($rec['user_type']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($rec['date_creation'])); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $rec['statut']; ?>">
                                    <?php echo ucfirst($rec['statut']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="gestion_reclamation.php?id=<?php echo $rec['id']; ?>" 
                                   class="btn btn-view">
                                    👁️ gestion
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="padding: 40px; text-align: center; color: #6c757d;">
                    <div style="font-size: 48px; margin-bottom: 20px;">📭</div>
                    <h3>Aucune réclamation</h3>
                    <p>Il n'y a aucune réclamation pour le moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>