<?php
require_once '../config.php';

// Vérifier si client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'client') {
    header('Location: ../login.php');
    exit();
}

$client_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Récupérer les projets du client
$projets = [];
try {
    $sql = "SELECT p.*, f.nom as freelancer_nom 
            FROM projets p 
            JOIN freelancers f ON p.freelancer_id = f.id 
            WHERE p.client_id = ? 
            ORDER BY p.date_creation DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$client_id]);
    $projets = $stmt->fetchAll();
} catch(Exception $e) {
    // Pas de problème si pas de projets
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $projet_id = intval($_POST['projet_id']);
    
    if (empty($titre) || empty($description)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $sql = "INSERT INTO reclamations (user_id, user_type, titre, description, statut) 
                    VALUES (?, 'client', ?, ?, 'ouverte')";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$client_id, $titre, $description]);
            
            $success = "Votre réclamation a été envoyée avec succès !";
            
            // Redirection après 2 secondes
            echo "<script>
                    setTimeout(function() {
                        window.location.href = 'mes_reclamations.php';
                    }, 2000);
                  </script>";
            
        } catch(Exception $e) {
            $error = "Erreur lors de l'envoi : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déposer une réclamation - FreelanceHub</title>
    <style>
        /* VOTRE CSS EXACT COMME LES AUTRES PAGES MAIS AVEC CADRE AGRANDI */
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

        /* Forms - CADRE TRÈS GRAND */
        .form-container {
            background: white;
            padding: 40px; /* TRÈS GRAND: de 60px à 70px */
            border-radius: 10px; /* Plus arrondi */
            max-width: 1500px; /* TRÈS LARGE: de 850px à 900px */
            margin: 50px auto;
            box-shadow: 0 15px 40px rgba(0,0,0,0.15); /* Ombre plus prononcée */
        }

        .form-group {
            margin-bottom: 35px; /* TRÈS GRAND: de 30px à 35px */
        }

        .form-group label {
            display: block;
            margin-bottom: 18px; /* TRÈS GRAND: de 15px à 18px */
            font-weight: bold;
            font-size: 20px; /* TRÈS GRAND: de 18px à 20px */
            color: #333;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 18px; /* TRÈS GRAND: de 16px à 18px */
            border: 2px solid #ddd; /* Bordure plus épaisse */
            border-radius: 8px; /* Plus arrondi */
            font-size: 18px; /* TRÈS GRAND: de 17px à 18px */
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
            font-size: 32px;
        }
        
        .page-header {
            margin-bottom: 50px;
        }
        
        .page-header h1 {
            margin-bottom: 15px;
        }
        
        .page-header p {
            color: #666;
            font-size: 18px;
        }
        
        .btn {
            display: inline-block;
            text-align: center;
            font-weight: 600;
            font-size: 16px;
            padding: 12px 25px;
        }
        
        .categories {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin: 25px 0; /* AUGMENTÉ: de 20px à 25px */
        }
        
        .category {
            flex: 1;
            min-width: 140px;
            padding: 18px; /* AUGMENTÉ: de 15px à 18px */
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 16px; /* AUGMENTÉ: de 15px à 16px */
        }
        
        .category:hover {
            border-color: #4361ee;
            background: #f0f5ff;
        }
        
        .category.selected {
            border-color: #4361ee;
            background: #4361ee;
            color: white;
        }
        
        .info-box {
            background: #e8f4fd;
            border-left: 4px solid #4361ee;
            padding: 25px;
            border-radius: 8px;
            margin-top: 40px;
        }
        
        .info-box h4 {
            margin-top: 0;
            color: #4361ee;
            font-size: 20px;
        }
        
        .info-box ul {
            color: #495057;
            padding-left: 20px;
            font-size: 15px;
        }
        
        .info-box li {
            margin-bottom: 10px;
        }
        
        .form-actions {
            display: flex;
            gap: 20px;
            margin-top: 50px; /* AUGMENTÉ: de 40px à 50px */
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
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
            color: #666;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .form-group textarea {
            min-height: 250px; /* TRÈS GRAND: de 200px à 250px */
            resize: vertical;
            font-size: 18px; /* AUGMENTÉ: de 17px à 18px */
        }
        
        select {
            font-size: 18px; /* AUGMENTÉ: de 17px à 18px */
        }
        
        .form-control {
            font-size: 18px; /* AUGMENTÉ */
        }
        
        .form-container form {
            max-width: 100%;
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
                    <a href="reclamation.php">Réclamation</a>
                    <a href="mes_reclamations.php">Mes réclamations</a>
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

    <main class="container"><br>
        <div class="page-header"><br>
            <h1>Déposer une réclamation</h1>
            <p>Signalez un problème rencontré avec un projet ou un freelancer</p>
            <div style="margin-top: 15px;">
                <a href="mes_reclamations.php" class="btn btn-secondary">
                    Voir mes réclamations
                </a>
            </div>
        </div>
        
        <!-- Statistiques utilisant vos classes CSS -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <div style="font-size: 20px; font-weight: bold; color: #4361ee; margin-bottom: 20px;">
                    0
                </div>
                <div style="color: #666;">Réclamations en cours</div>
            </div>
            <div class="stat-card">
                <div style="font-size: 20px; font-weight: bold; color: #4361ee; margin-bottom: 20px;">
                    0
                </div>
                <div style="color: #666;">Réclamations résolues</div>
            </div>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
                <p>Redirection vers vos réclamations...</p>
            </div>
        <?php endif; ?>
        
        <!-- Formulaire de réclamation avec CADRE TRÈS GRAND -->
        <div class="form-container">
            <form method="POST" action="">
                <!-- Projet concerné -->
                <div class="form-group">
                    <label>Projet concerné (optionnel)</label>
                    <select name="projet_id" class="form-control">
                        <option value="0">-- Pas de projet spécifique --</option>
                        <option value="1">-- Site vitrine --</option>
                        <option value="2">-- Application Android--</option>
                        <option value="3">-- Logo moderne --</option>
                        <option value="4">-- Blog WordPress --</option>
                        <option value="5">-- Portfolio designer --</option>
                        <?php if(count($projets) > 0): ?>
                            <?php foreach($projets as $projet): ?>
                                <option value="<?php echo $projet['id']; ?>">
                                    Projet #<?php echo $projet['id']; ?>: <?php echo htmlspecialchars($projet['titre']); ?>
                                    (Freelancer: <?php echo htmlspecialchars($projet['freelancer_nom']); ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="0">Aucun projet disponible</option>
                        <?php endif; ?>
                    </select>
                </div>
                
                <!-- Titre -->
                <div class="form-group">
                    <label>Titre de la réclamation *</label>
                    <input type="text" name="titre" class="form-control" required 
                           placeholder="Ex: Problème avec le freelancer">
                </div>
                
                <!-- Catégorie (visuel seulement) -->
                <div class="form-group">
                    <label>Type de problème (information)</label>
                    <div class="categories">
                        <div class="category" onclick="selectCategory(this)">
                            Retard
                        </div>
                        <div class="category" onclick="selectCategory(this)">
                            Qualité
                        </div>
                        <div class="category" onclick="selectCategory(this)">
                            Communication
                        </div>
                        <div class="category" onclick="selectCategory(this)">
                            Autre
                        </div>
                    </div>
                    <p style="color: #666; font-size: 14px; margin-top: 10px;">
                        <i>Cette information n'est pas enregistrée pour l'instant</i>
                    </p>
                </div>
                
                <!-- Description -->
                <div class="form-group">
                    <label>Description détaillée *</label>
                    <textarea name="description" class="form-control" required 
                              placeholder="Décrivez le problème en détail..."></textarea>
                </div>
                
                <!-- Boutons -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        Envoyer la réclamation
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Information -->
        <div class="info-box">
            <h4>Informations importantes</h4>
            <ul>
                <li><strong>Traitement rapide :</strong> Votre réclamation sera traitée sous 48h maximum</li>
                <li><strong>Contact :</strong> Un administrateur vous contactera si des informations supplémentaires sont nécessaires</li>
                <li><strong>Suivi :</strong> Vous pouvez suivre l'avancement de votre réclamation dans la section "Mes réclamations"</li>
                <li><strong>Précision :</strong> Soyez précis dans votre description pour un traitement plus rapide</li>
                <li><strong>Confidentialité :</strong> Vos informations sont traitées de manière confidentielle</li>
            </ul>
        </div>
        
        <!-- Lien rapide vers mes réclamations -->
        <div style="text-align: center; margin-top: 30px;">
            <a href="mes_reclamations.php" class="btn" style="background: #f8f9fa; color: #333; border: 1px solid #ddd;">
                Consulter l'historique de mes réclamations
            </a>
        </div>
    </main>
    
    <script>
        // Gestion visuelle des catégories
        function selectCategory(element) {
            // Retirer la sélection de toutes les catégories
            document.querySelectorAll('.category').forEach(cat => {
                cat.classList.remove('selected');
            });
            
            // Ajouter la sélection à la catégorie cliquée
            element.classList.add('selected');
        }
        
        // Sélectionner une catégorie par défaut
        document.addEventListener('DOMContentLoaded', function() {
            const lastCategory = document.querySelectorAll('.category')[3];
            if (lastCategory) {
                selectCategory(lastCategory);
            }
        });
    </script>
</body>
</html>