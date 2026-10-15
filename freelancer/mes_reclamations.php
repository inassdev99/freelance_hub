<?php
require_once '../config.php';

// Vérifier si freelancer est connecté
if (!estConnecte() || !isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'freelancer') {
    rediriger('login.php');
}

$user_id = $_SESSION['user_id'];

try {
    // Récupérer les réclamations du freelancer
    $stmt = $conn->prepare("SELECT r.* 
                           FROM reclamations r
                           WHERE r.user_id = ? AND r.user_type = 'freelancer'
                           ORDER BY r.date_creation DESC");
    $stmt->execute([$user_id]);
    $reclamations = $stmt->fetchAll();
    
} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Réclamations - FreelanceHub</title>
    <style>
        /* VOTRE CSS EXACT COMME LA PAGE CLIENT */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f5f5f5;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .header {
            background: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #4361ee;
            text-decoration: none;
        }

        .nav-menu {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-menu a {
            color: #333;
            text-decoration: none;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #4361ee;
            color: white;
        }

        .btn-secondary {
            background: #7209b7;
            color: white;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #4361ee;
            color: #4361ee;
        }

        /* Forms */
        .form-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            max-width: 500px;
            margin: 100px auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        /* Tables */
        .table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            margin: 30px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            font-weight: bold;
        }

        /* Dashboard - STATISTIQUES PLUS PETITES */
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 20px 0 30px 0;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        /* Alerts */
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        /* Footer */
        .footer {
            background: #333;
            color: white;
            padding: 50px 0;
            text-align: center;
            margin-top: 100px;
        }
        
        /* Styles supplémentaires pour cette page */
        .nav-active {
            color: #4361ee;
            font-weight: bold;
        }
        
        main {
            margin-top: 80px;
        }
        
        h1 {
            margin-bottom: 20px;
            color: #333;
            font-size: 28px;
        }
        
        .page-header {
            margin-bottom: 25px;
        }
        
        .page-header h1 {
            margin-bottom: 8px;
        }
        
        .page-header p {
            color: #666;
            font-size: 15px;
        }
        
        .btn {
            display: inline-block;
            text-align: center;
            font-weight: 600;
            font-size: 14px;
            padding: 8px 16px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .card-date {
            color: #666;
            font-size: 13px;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
        }
        
        .badge-ouverte { 
            background: #ffeaa7;
            color: #d63031;
        }
        
        .badge-en_cours { 
            background: #74b9ff;
            color: #0984e3;
        }
        
        .badge-resolue { 
            background: #55efc4;
            color: #00b894;
        }
        
        .description {
            color: #444;
            line-height: 1.6;
            margin: 15px 0;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .reponse-container {
            margin-top: 20px;
            padding: 15px;
            background: #e8f5e9;
            border-radius: 8px;
            border-left: 3px solid #2e7d32;
        }
        
        .reponse-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            color: #2e7d32;
            font-size: 14px;
        }
        
        .reponse-title {
            font-weight: bold;
        }
        
        .reponse-date {
            font-size: 12px;
            color: #5d99c6;
        }
        
        .reponse-content {
            color: #2e7d32;
            line-height: 1.6;
            padding: 8px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 5px;
            font-size: 14px;
        }
        
        .no-reponse {
            padding: 15px;
            background: #fff8e1;
            border-radius: 8px;
            color: #ff8f00;
            text-align: center;
            border: 1px solid #ffd54f;
            margin-top: 15px;
            font-size: 14px;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            background: white;
            border-radius: 10px;
            color: #666;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
            font-size: 20px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: 20px;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            background: #4361ee;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        
        .info-box {
            background: #e8f4fd;
            border-left: 3px solid #4361ee;
            padding: 15px;
            border-radius: 6px;
            margin-top: 25px;
            font-size: 14px;
        }
        
        .info-box h3 {
            color: #4361ee;
            margin-top: 0;
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .info-box ul {
            color: #495057;
            padding-left: 20px;
            margin: 0;
        }
        
        .info-box li {
            margin-bottom: 6px;
        }
        
        /* Stat card numbers plus petits */
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #4361ee;
            display: block;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 13px;
        }
        
        @media (max-width: 768px) {
            .dashboard-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .nav-menu {
                gap: 10px;
            }
            
            .actions {
                flex-direction: column;
            }
        }
        
        @media (max-width: 480px) {
            .dashboard-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Utilisation de votre structure CSS exacte comme la page client -->
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="../index.php" class="logo">FreelanceHub</a>
                <div class="nav-menu">
                    <a href="dashboard.php">Tableau de bord</a>
                    <a href="mes_reclamations.php">Mes réclamations</a>
                    <div class="user-info">
                        <?php if(isset($_SESSION['user_name'])): ?>
                            <div class="user-avatar">
                                <?php echo substr($_SESSION['user_name'], 0, 1); ?>
                            </div>
                            <span style="font-size: 14px;"><?php echo $_SESSION['user_name']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <main class="container"><br>
        <div class="page-header">
            <h1>Mes Réclamations</h1>
            <p>Suivez l'état de vos réclamations et les réponses du support</p>
            <div style="margin-top: 10px;">
                <a href="reclamation.php" class="btn btn-primary">
                    Nouvelle réclamation
                </a>
            </div>
        </div>
        
        <!-- Statistiques PLUS PETITES - Même que client -->
        <?php 
        $ouvertes = array_filter($reclamations, fn($r) => $r['statut'] == 'ouverte');
        $resolues = array_filter($reclamations, fn($r) => $r['statut'] == 'resolue');
        ?>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <span class="stat-number"><?php echo count($reclamations); ?></span>
                <span class="stat-label">Total réclamations</span>
            </div>
            
            <div class="stat-card">
                <span class="stat-number"><?php echo count($ouvertes); ?></span>
                <span class="stat-label">En attente</span>
            </div>
            
            <div class="stat-card">
                <span class="stat-number"><?php echo count($resolues); ?></span>
                <span class="stat-label">Résolues</span>
            </div>
        </div>
        
        <?php if(empty($reclamations)): ?>
            <div class="empty-state">
                <h3>Aucune réclamation trouvée</h3>
                <p>Vous n'avez pas encore déposé de réclamation.</p>
                <a href="deposer_reclamation.php" class="btn btn-primary" style="margin-top: 15px;">
                    Déposer ma première réclamation
                </a>
            </div>
        <?php else: ?>
            <?php foreach($reclamations as $reclamation): ?>
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">
                                #<?php echo $reclamation['id']; ?> - <?php echo htmlspecialchars($reclamation['titre']); ?>
                            </div>
                            <div class="card-date">
                                <?php echo date('d/m/Y à H:i', strtotime($reclamation['date_creation'])); ?>
                            </div>
                        </div>
                        <div>
                            <span class="badge badge-<?php echo $reclamation['statut']; ?>">
                                <?php echo ucfirst($reclamation['statut']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="description">
                        <strong style="color: #333; display: block; margin-bottom: 8px; font-size: 14px;">
                            Description :
                        </strong>
                        <?php echo nl2br(htmlspecialchars($reclamation['description'])); ?>
                    </div>
                    
                    <!-- Affichage de la réponse admin -->
                    <?php if(!empty($reclamation['reponse_admin'])): ?>
                        <div class="reponse-container">
                            <div class="reponse-header">
                                <div class="reponse-title">
                                    Réponse du support
                                </div>
                                <div class="reponse-date">
                                    <?php if(!empty($reclamation['date_reponse'])): ?>
                                        <?php echo date('d/m/Y H:i', strtotime($reclamation['date_reponse'])); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="reponse-content">
                                <?php echo nl2br(htmlspecialchars($reclamation['reponse_admin'])); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-reponse">
                            <strong>En attente de réponse</strong>
                            <p style="margin: 8px 0 0 0; font-size: 13px;">
                                Notre équipe de support examinera votre demande.
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="actions">
                        <a href="detail_reclamation.php?id=<?php echo $reclamation['id']; ?>" class="btn btn-primary">
                            Voir détails
                        </a>
                        
                        <?php if($reclamation['statut'] !== 'resolue'): ?>
                            <a href="deposer_reclamation.php?suivi=<?php echo $reclamation['id']; ?>" class="btn btn-secondary">
                                Ajouter suivi
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <!-- Information - Même que client -->
        <div class="info-box">
            <h3>Informations importantes</h3>
            <ul>
                <li><strong>Traitement rapide :</strong> Votre réclamation sera traitée sous 48h maximum</li>
                <li><strong>Contact :</strong> Un administrateur vous contactera si besoin</li>
                <li><strong>Suivi :</strong> Vous pouvez suivre l'avancement de vos réclamations ici</li>
                <li><strong>Confidentialité :</strong> Vos informations sont traitées confidentiellement</li>
            </ul>
        </div>
    </main>
</body>
</html>