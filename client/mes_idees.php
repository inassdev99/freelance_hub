<?php
require_once '../config.php';

// Vérifier si client est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'client') {
    header('Location: ../login.php');
    exit();
}

$client_id = $_SESSION['user_id'];

// Récupérer les idées du client
$sql = "SELECT i.*, 
               (SELECT COUNT(*) FROM propositions p WHERE p.idee_id = i.id) as nb_propositions
        FROM idees i
        WHERE i.client_id = ?
        ORDER BY i.id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute([$client_id]);
$idees = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Idées - FreelanceHub</title>
    <style>
        /* VOTRE CSS EXACT COMME LES AUTRES PAGES */
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

        /* Dashboard */
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 30px 0;
        }

        .stat-card {
            background: white;
            padding: 30px;
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
            margin-bottom: 30px;
            color: #333;
        }
        
        .page-header {
            margin-bottom: 40px;
        }
        
        .page-header h1 {
            margin-bottom: 10px;
        }
        
        .page-header p {
            color: #666;
        }
        
        .btn {
            display: inline-block;
            text-align: center;
            font-weight: 600;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .card-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .card-date {
            color: #666;
            font-size: 14px;
        }
        
        .badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
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
        
        .badge-terminee { 
            background: #55efc4;
            color: #00b894;
        }
        
        .badge-annulee { 
            background: #ff7675;
            color: #d63031;
        }
        
        .proposition-badge {
            background: #7209b7;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin-left: 10px;
        }
        
        .description {
            color: #444;
            line-height: 1.6;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .info-item {
            padding: 12px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .info-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-weight: 600;
            color: #333;
        }
        
        .card-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
            color: #666;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: 20px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: #4361ee;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Utilisation de votre structure CSS exacte -->
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="../index.php" class="logo">FreelanceHub</a>
                <div class="nav-menu">
                    <a href="dashboard.php">Tableau de bord</a>
                    <a href="ajouter_idee.php">Publier une idée</a>
                    <a href="mes_idees.php">Mes idées</a>
                    <div class="user-info">
                        <?php if(isset($_SESSION['user_name'])): ?>
                            <div class="user-avatar">
                                <?php echo substr($_SESSION['user_name'], 0, 1); ?>
                            </div>
                            <span><?php echo $_SESSION['user_name']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="page-header"><br>
            <h1>Mes Idées</h1>
            <p>Gérez vos projets publiés et les propositions reçues</p>
            <div style="margin-top: 15px;">
                <a href="ajouter_idee.php" class="btn btn-primary">
                    Nouvelle idée
                </a>
            </div>
        </div>
        
        <?php if(empty($idees)): ?>
            <div class="empty-state">
                <h3>Aucune idée publiée</h3>
                <p>Vous n'avez pas encore publié d'idée de projet.</p>
                <a href="ajouter_idee.php" class="btn btn-primary" style="margin-top: 20px;">
                    Publier ma première idée
                </a>
            </div>
        <?php else: ?>
            <?php foreach($idees as $idee): ?>
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">
                                #<?php echo $idee['id']; ?> - <?php echo htmlspecialchars($idee['titre']); ?>
                            </div>
                            <div class="card-date">
                                <?php 
                                    if(isset($idee['created_at']) && !empty($idee['created_at'])) {
                                        echo date('d/m/Y H:i', strtotime($idee['created_at']));
                                    } else {
                                        echo 'Date inconnue';
                                    }
                                ?>
                            </div>
                        </div>
                        <div>
                            <span class="badge badge-<?php echo $idee['statut']; ?>">
                                <?php echo ucfirst($idee['statut']); ?>
                            </span>
                            
                            <?php if($idee['nb_propositions'] > 0): ?>
                                <span class="proposition-badge">
                                    <?php echo $idee['nb_propositions']; ?> proposition(s)
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="description">
                        <strong style="color: #333; display: block; margin-bottom: 10px;">
                            Description :
                        </strong>
                        <?php echo nl2br(htmlspecialchars($idee['description'])); ?>
                    </div>
                    
                    <div class="info-grid">
                        <?php if(!empty($idee['categorie'])): ?>
                            <div class="info-item">
                                <div class="info-label">Catégorie</div>
                                <div class="info-value"><?php echo htmlspecialchars($idee['categorie']); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(isset($idee['budget']) && $idee['budget'] > 0): ?>
                            <div class="info-item">
                                <div class="info-label">Budget</div>
                                <div class="info-value"><?php echo number_format($idee['budget'], 2); ?> MAD</div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(!empty($idee['delai'])): ?>
                            <div class="info-item">
                                <div class="info-label">Délai souhaité</div>
                                <div class="info-value">
                                    <?php 
                                        $delai = $idee['delai'];
                                        if ($delai && $delai != '0000-00-00') {
                                            echo date('d/m/Y', strtotime($delai));
                                        } else {
                                            echo 'Non spécifié';
                                        }
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-actions">
                        <a href="detail_idee.php?id=<?php echo $idee['id']; ?>" class="btn btn-primary">
                            Voir les détails
                        </a>
                        
                        <?php if($idee['statut'] == 'ouverte'): ?>
                            <a href="modifier_idee.php?id=<?php echo $idee['id']; ?>" class="btn btn-secondary">
                                Modifier
                            </a>
                            
                            <a href="archiver_idee.php?id=<?php echo $idee['id']; ?>" class="btn btn-warning">
                                Archiver
                            </a>
                        <?php endif; ?>
                        
                        <?php if($idee['nb_propositions'] > 0): ?>
                            <a href="propositions.php?idee_id=<?php echo $idee['id']; ?>" class="btn btn-success">
                                Voir propositions (<?php echo $idee['nb_propositions']; ?>)
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</body>
</html>