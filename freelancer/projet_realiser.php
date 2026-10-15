<?php
require_once '../config.php';

// Vérifier si freelancer est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'freelancer') {
    header('Location: ../login.php');
    exit();
}

$freelancer_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Récupérer les projets déjà réalisés du freelancer
$projets_realises = [];
try {
    $sql = "SELECT * FROM projets_realises 
            WHERE freelancer_id = ? 
            ORDER BY date_ajout DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$freelancer_id]);
    $projets_realises = $stmt->fetchAll();
} catch(Exception $e) {
    // Table peut ne pas exister encore
}

// Traitement du formulaire d'ajout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $categorie = trim($_POST['categorie']);
    $technologies = trim($_POST['technologies']);
    $lien_github = trim($_POST['lien_github']);
    $prix = trim($_POST['prix']);
    
    if (empty($titre) || empty($description) || empty($categorie) || empty($prix)) {
        $error = "Veuillez remplir les champs obligatoires (titre, description, catégorie, prix).";
    } else {
        try {
            // Créer la table si elle n'existe pas
            $sql_create = "CREATE TABLE IF NOT EXISTS projets_realises (
                id INT PRIMARY KEY AUTO_INCREMENT,
                freelancer_id INT NOT NULL,
                titre VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                categorie VARCHAR(100) NOT NULL,
                technologies TEXT,
                lien_github VARCHAR(500),
                prix DECIMAL(10,2) NOT NULL,
                date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (freelancer_id) REFERENCES freelancers(id)
            )";
            $conn->exec($sql_create);
            
            // Insérer le projet
            $sql = "INSERT INTO projets_realises 
                   (freelancer_id, titre, description, categorie, technologies, lien_github, prix) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $freelancer_id, 
                $titre, 
                $description, 
                $categorie, 
                $technologies,
                $lien_github,
                $prix
            ]);
            
            $success = "Votre projet a été ajouté avec succès à votre portfolio !";
            
            // Rafraîchir la liste
            $stmt = $conn->prepare("SELECT * FROM projets_realises WHERE freelancer_id = ? ORDER BY date_ajout DESC");
            $stmt->execute([$freelancer_id]);
            $projets_realises = $stmt->fetchAll();
            
        } catch(Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

// Traitement suppression
if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id'])) {
    $projet_id = intval($_GET['id']);
    
    try {
        // Vérifier que le projet appartient bien au freelancer
        $sql = "SELECT id FROM projets_realises WHERE id = ? AND freelancer_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$projet_id, $freelancer_id]);
        
        if ($stmt->fetch()) {
            // Supprimer le projet
            $sql_delete = "DELETE FROM projets_realises WHERE id = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->execute([$projet_id]);
            
            $success = "Projet supprimé avec succès.";
            
            // Rafraîchir la liste
            $stmt = $conn->prepare("SELECT * FROM projets_realises WHERE freelancer_id = ? ORDER BY date_ajout DESC");
            $stmt->execute([$freelancer_id]);
            $projets_realises = $stmt->fetchAll();
        }
    } catch(Exception $e) {
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Projets Réalisés - FreelanceHub</title>
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
            font-weight: 600;
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

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        /* Forms - CADRE AGRANDI */
        .form-container {
            background: white;
            padding: 50px;
            border-radius: 12px;
            max-width: 1500px;
            margin: 50px auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 12px;
            font-weight: bold;
            font-size: 16px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
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

        /* Dashboard */
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
            font-size: 32px;
        }
        
        .page-header {
            margin-bottom: 40px;
        }
        
        .page-header h1 {
            margin-bottom: 15px;
        }
        
        .page-header p {
            color: #666;
            font-size: 18px;
        }
        
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
        
        /* Cards pour projets */
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .project-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .project-card:hover {
            transform: translateY(-5px);
        }
        
        .project-icon {
            width: 100%;
            height: 150px;
            background: #4361ee;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
        }
        
        .project-content {
            padding: 20px;
        }
        
        .project-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .project-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .project-tech {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 15px;
        }
        
        .tech-tag {
            background: #e0e7ff;
            color: #4361ee;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .project-links {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .project-link {
            padding: 8px 15px;
            background: #f8f9fa;
            color: #4361ee;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .project-link:hover {
            background: #4361ee;
            color: white;
        }
        
        .project-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
            color: #666;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-top: 30px;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
        }
        
        .price-tag {
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            display: inline-block;
            margin-top: 10px;
        }
        
        @media (max-width: 768px) {
            .form-row, .form-row-3 {
                grid-template-columns: 1fr;
            }
            
            .projects-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-stats {
                grid-template-columns: 1fr;
            }
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
                    <a href="dashboard.php">tableau de bord</a>
                    <a href="projet_realiser.php">mes projets</a>
                    <div class="user-info" style="display: flex; align-items: center; gap: 10px;">
                        <?php if(isset($_SESSION['user_name'])): ?>
                            <span><?php echo $_SESSION['user_name']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <main class="container"><br>
        <div class="page-header">
            <h1>les projets realiser</h1>
            <p>Ajoutez vos projets réalisés pour montrer votre expérience aux clients</p>
        </div>
        
        <!-- Statistiques -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <span class="stat-number"><?php echo count($projets_realises); ?></span>
                <span class="stat-label">Projets dans mon portfolio</span>
            </div>
            
            <div class="stat-card">
                <span class="stat-number">0</span>
                <span class="stat-label">Vues ce mois</span>
            </div>
            
            <div class="stat-card">
                <span class="stat-number">0</span>
                <span class="stat-label">Clients intéressés</span>
            </div>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Formulaire d'ajout de projet -->
        <div class="form-container">
            <h2 style="margin-bottom: 30px; color: #333; text-align: center;">Ajouter un projet réalisé</h2>
            
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label>Titre du projet *</label>
                        <input type="text" name="titre" required placeholder="Ex: Site e-commerce pour boutique de mode">
                    </div>
                    
                    <div class="form-group">
                        <label>Catégorie *</label>
                        <select name="categorie" required>
                            <option value="">Sélectionnez une catégorie</option>
                            <option value="web">Développement Web</option>
                            <option value="mobile">Application Mobile</option>
                            <option value="design">Design UI/UX</option>
                            <option value="marketing">Marketing Digital</option>
                            <option value="seo">SEO/Referencement</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Description détaillée *</label>
                    <textarea name="description" rows="4" required 
                              placeholder="Décrivez le projet, les fonctionnalités, les défis rencontrés, etc..."></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Technologies utilisées (séparées par des virgules)</label>
                        <input type="text" name="technologies" 
                               placeholder="Ex: PHP, MySQL, JavaScript, React, Node.js">
                    </div>
                    
                    <div class="form-group">
                        <label>Lien GitHub (optionnel)</label>
                        <input type="url" name="lien_github" placeholder="https://github.com/votre-projet">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Prix du projet (MAD) *</label>
                    <input type="number" name="prix" required min="0" step="0.01" 
                           placeholder="Ex: 5000.00">
                </div>
                
                <div class="form-actions" style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">
                        Ajouter au portfolio
                    </button>
                    <button type="reset" class="btn btn-outline">
                        Effacer le formulaire
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Liste des projets existants -->
        <div style="margin-top: 50px;">
            <h2 style="margin-bottom: 20px; color: #333;">Mes projets réalisés</h2>
            
            <?php if(empty($projets_realises)): ?>
                <div class="empty-state">
                    <h3>Votre portfolio est vide</h3>
                    <p>Ajoutez vos premiers projets pour montrer votre expérience aux clients.</p>
                    <p style="color: #666; font-size: 14px; margin-top: 10px;">
                        Les clients sont plus susceptibles de choisir des freelancers avec un portfolio complet.
                    </p>
                </div>
            <?php else: ?>
                <div class="projects-grid">
                    <?php foreach($projets_realises as $projet): ?>
                        <div class="project-card">
                            <div class="project-icon">
                                <i class="fas fa-code"></i>
                            </div>
                            
                            <div class="project-content">
                                <div class="project-title"><?php echo htmlspecialchars($projet['titre']); ?></div>
                                
                                <div style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="background: #f0f5ff; color: #4361ee; padding: 3px 8px; border-radius: 12px; font-size: 12px;">
                                        <?php echo htmlspecialchars($projet['categorie']); ?>
                                    </span>
                                    <span class="price-tag">
                                        <?php echo number_format($projet['prix'], 2, ',', ' '); ?> MAD
                                    </span>
                                </div>
                                
                                <div style="font-size: 12px; color: #666; margin-bottom: 10px;">
                                    Ajouté le <?php echo date('d/m/Y', strtotime($projet['date_ajout'])); ?>
                                </div>
                                
                                <div class="project-description">
                                    <?php echo nl2br(htmlspecialchars(substr($projet['description'], 0, 150) . '...')); ?>
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
                                
                                <?php if(!empty($projet['lien_github'])): ?>
                                    <div class="project-links">
                                        <a href="<?php echo htmlspecialchars($projet['lien_github']); ?>" 
                                           target="_blank" class="project-link">
                                            <i class="fab fa-github"></i> Code GitHub
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="project-actions">
                                    <a href="modifier_projet.php?id=<?php echo $projet['id']; ?>" class="btn btn-outline" style="padding: 8px 15px; font-size: 14px;">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <a href="?action=supprimer&id=<?php echo $projet['id']; ?>" 
                                       class="btn btn-danger" 
                                       style="padding: 8px 15px; font-size: 14px;"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce projet ?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Information -->
        <div style="background: #e8f4fd; border-left: 4px solid #4361ee; padding: 20px; border-radius: 6px; margin-top: 40px;">
            <h3 style="color: #4361ee; margin-top: 0;">Conseils pour votre portfolio</h3>
            <ul style="color: #495057; padding-left: 20px;">
                <li><strong>Qualité avant quantité :</strong> Mieux vaut quelques projets bien présentés que beaucoup mal décrits</li>
                <li><strong>Détails techniques :</strong> Mentionnez les technologies, frameworks et outils utilisés</li>
                <li><strong>Résultats :</strong> Si possible, indiquez les résultats obtenus (trafic généré, conversions, etc.)</li>
                <li><strong>Prix :</strong> Indiquez le prix du projet pour donner une idée de votre tarification</li>
                <li><strong>Code source :</strong> Ajoutez un lien GitHub pour montrer la qualité de votre code</li>
                <li><strong>Mettez à jour :</strong> Ajoutez régulièrement vos nouveaux projets</li>
            </ul>
        </div>
    </main>
    
    <!-- Icônes FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>