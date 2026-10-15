<?php
require_once '../config.php';

// Vérifier si client est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'client') {
    header('Location: ../login.php');
    exit();
}

// Récupérer tous les projets réalisés avec les infos freelancer
$projets_realises = [];
try {
    $sql = "SELECT pr.*, f.nom as freelancer_nom 
            FROM projets_realises pr 
            JOIN freelancers f ON pr.freelancer_id = f.id 
            ORDER BY pr.date_ajout DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $projets_realises = $stmt->fetchAll();
} catch(Exception $e) {
    // Table peut ne pas exister
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio des Freelancers</title>
    <style>
        /* VOTRE CSS EXISTANT */
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
        }

        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .project-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .project-header {
            padding: 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
        }

        .project-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .project-freelancer {
            color: #666;
            font-size: 14px;
        }

        .project-body {
            padding: 15px;
        }

        .project-price {
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
        }

        .project-description {
            color: #666;
            line-height: 1.5;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .project-tech {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin: 10px 0;
        }

        .tech-tag {
            background: #e0e7ff;
            color: #4361ee;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
        }

        .project-footer {
            padding: 15px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
            text-align: center;
        }

        .btn {
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-primary {
            background: #4361ee;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: white;
            border-radius: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Header Client -->
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="../index.php" class="logo">FreelanceHub</a>
                <div class="nav-menu">
                    <a href="dashboard.php">Tableau de bord</a>
                    <a href="voir_projet_realiser.php">Projet realiser</a>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <?php if(isset($_SESSION['user_name'])): ?>
                            <span>Client : <?php echo $_SESSION['user_name']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <main class="container"><br>
        <div style="margin-bottom: 30px;">
            <h1>Portfolio des Freelancers</h1>
            <p style="color: #666;">Découvrez les projets réalisés par nos freelancers</p>
        </div>
        
        <!-- Liste des projets -->
        <?php if(empty($projets_realises)): ?>
            <div class="empty-state">
                <h3>Aucun projet disponible</h3>
                <p>Les freelancers n'ont pas encore ajouté de projets à leur portfolio.</p>
            </div>
        <?php else: ?>
            <div class="portfolio-grid">
                <?php foreach($projets_realises as $projet): ?>
                    <div class="project-card">
                        <div class="project-header">
                            <div class="project-title"><?php echo htmlspecialchars($projet['titre']); ?></div>
                            <div class="project-freelancer">
                                Par : <?php echo htmlspecialchars($projet['freelancer_nom']); ?>
                            </div>
                        </div>
                        
                        <div class="project-body">
                            <div class="project-price">
                                <?php echo number_format($projet['prix'], 2, ',', ' '); ?> MAD
                            </div>
                            
                            <div class="project-description">
                                <?php echo nl2br(htmlspecialchars(substr($projet['description'], 0, 100))); ?>
                                <?php if(strlen($projet['description']) > 100): ?>...<?php endif; ?>
                            </div>
                            
                            <?php if(!empty($projet['technologies'])): ?>
                                <div class="project-tech">
                                    <?php 
                                    $techs = explode(',', $projet['technologies']);
                                    foreach($techs as $tech): 
                                        $tech = trim($tech);
                                        if(!empty($tech)): ?>
                                            <span class="tech-tag"><?php echo htmlspecialchars($tech); ?></span>
                                        <?php endif;
                                    endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div style="font-size: 12px; color: #999; margin-top: 10px;">
                                Catégorie : <?php echo htmlspecialchars($projet['categorie']); ?>
                            </div>
                        </div>
                        
                        <div class="project-footer">
                            <button class="btn btn-primary" 
                                    onclick="alert('Fonctionnalité de contact à implémenter')">
                                Contacter le freelancer
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>