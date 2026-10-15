<?php
require_once '../config.php';

// Vérifier si freelancer est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'freelancer') {
    header('Location: ../login.php');
    exit();
}

// Test direct - voir toutes les idées dans la base de données
$sql_test = "SELECT * FROM idees";
$stmt_test = $conn->prepare($sql_test);
$stmt_test->execute();
$toutes_idees = $stmt_test->fetchAll(PDO::FETCH_ASSOC);

// Récupérer TOUTES les idées des clients (sans filtrer pour voir ce qui existe)
$sql = "SELECT idees.*, clients.nom as client_nom, clients.email as client_email
        FROM idees 
        LEFT JOIN clients ON idees.client_id = clients.id
        WHERE idees.statut IS NOT NULL";
        
$stmt = $conn->prepare($sql);
$stmt->execute();
$idees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreelanceHub - Idées des Clients</title>
    <style>
        /* VOTRE CSS EXACT TEL QUEL */
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

        /* Hero */
        .hero {
            background: #4361ee;
            color: white;
            padding: 100px 0;
            text-align: center;
        }

        .hero h1 {
            font-size: 36px;
            margin-bottom: 20px;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 30px;
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

        /* Styles spécifiques pour cette page - uniquement des compléments */
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
        
        .card-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }
        
        .card-client {
            color: #666;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .description {
            color: #444;
            line-height: 1.6;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        
        .badge-ouverte { 
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .badge-en_cours { 
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .badge-terminee { 
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        .badge-annulee { 
            background: #ffebee;
            color: #c62828;
        }
        
        .budget {
            background: #4361ee;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin-left: 10px;
        }
        
        .skills-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 15px 0;
        }
        
        .skill-tag {
            background: #e0e7ff;
            color: #4361ee;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
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
        
        .card-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
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
                    <a href="idees.php">Idées des clients</a>
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
            <h1>Idées des Clients</h1>
            <p>Trouvez des projets qui correspondent à vos compétences</p>
        </div>
        
        <!-- Statistiques utilisant vos classes CSS -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <div style="font-size: 36px; font-weight: bold; color: #4361ee; margin-bottom: 10px;">
                    <?php echo count($idees); ?>
                </div>
                <div style="color: #666;">Idées disponibles</div>
            </div>
            <div class="stat-card">
                <div style="font-size: 36px; font-weight: bold; color: #4361ee; margin-bottom: 10px;">
                    5
                </div>
                <div style="color: #666;">Mes projets</div>
            </div>
        </div>
        
        <?php if(empty($idees)): ?>
            <div class="empty-state">
                <h3>Aucune idée disponible</h3>
                <p>Il n'y a pas d'idées de projets pour le moment.</p>
                <p style="color: #666; font-size: 14px; margin-top: 10px;">
                    Les clients peuvent publier des idées via leur espace.
                </p>
            </div>
        <?php else: ?>
            <?php foreach($idees as $idee): ?>
                <div class="card">
                    <div class="card-title">
                        #<?php echo $idee['id']; ?> - <?php echo htmlspecialchars($idee['titre']); ?>
                    </div>
                    <div class="card-client">
                        Client: <?php echo htmlspecialchars($idee['client_nom'] ?? 'Anonyme'); ?>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <span class="badge badge-<?php echo $idee['statut']; ?>">
                            <?php echo ucfirst($idee['statut']); ?>
                        </span>
                        <?php if(isset($idee['budget']) && $idee['budget'] > 0): ?>
                            <span class="budget">
                                <?php echo number_format($idee['budget'], 2); ?> MAD
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="description">
                        <strong style="color: #333; display: block; margin-bottom: 10px;">
                            Description :
                        </strong>
                        <?php echo nl2br(htmlspecialchars($idee['description'])); ?>
                    </div>
                    
                    <?php if(!empty($idee['competences_requises'])): ?>
                        <div class="skills-container">
                            <?php 
                            $competences = explode(',', $idee['competences_requises']);
                            foreach($competences as $competence): 
                                $competence = trim($competence);
                                if(!empty($competence)): ?>
                                    <span class="skill-tag">
                                        <?php echo htmlspecialchars($competence); ?>
                                    </span>
                                <?php endif;
                            endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="info-grid">
                        <?php if(!empty($idee['categorie'])): ?>
                            <div class="info-item">
                                <div class="info-label">Catégorie</div>
                                <div class="info-value"><?php echo htmlspecialchars($idee['categorie']); ?></div>
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
                        
                        <?php if(isset($idee['created_at']) && !empty($idee['created_at'])): ?>
                            <div class="info-item">
                                <div class="info-label">Date de publication</div>
                                <div class="info-value">
                                    <?php 
                                        $date = $idee['created_at'];
                                        if ($date && $date != '0000-00-00 00:00:00') {
                                            echo date('d/m/Y H:i', strtotime($date));
                                        } else {
                                            echo 'Date inconnue';
                                        }
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(!empty($idee['client_email'])): ?>
                            <div class="info-item">
                                <div class="info-label">Contact</div>
                                <div class="info-value"><?php echo htmlspecialchars($idee['client_email']); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-actions">
                        <a href="postuler.php?id=<?php echo $idee['id']; ?>" class="btn btn-primary">
                            Postuler
                        </a>
                        
                        <?php if(!empty($idee['client_email'])): ?>
                            <a href="mailto:<?php echo htmlspecialchars($idee['client_email']); ?>?subject=Proposition pour votre idée: <?php echo urlencode($idee['titre']); ?>" 
                               class="btn btn-secondary">
                                Contacter le client
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</body>
</html>