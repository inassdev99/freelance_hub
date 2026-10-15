<?php
require_once '../config.php';

// Vérifier si client est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'client') {
    header('Location: ../login.php');
    exit();
}

$client_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titre = $_POST['titre'] ?? '';
    $description = $_POST['description'] ?? '';
    $budget = $_POST['budget'] ?? 0;
    
    if (empty($titre) || empty($description)) {
        $error = "Veuillez remplir tous les champs obligatoires";
    } else {
        try {
            $sql = "INSERT INTO idees (client_id, titre, description, budget) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$client_id, $titre, $description, $budget]);
            
            $success = "Votre idée a été publiée avec succès !";
        } catch(Exception $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publier une idée</title>
    <!-- VOTRE CSS MODIFIÉ POUR UN FORMULAIRE PLUS GRAND -->
    <style>
        /* style.css */
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
            box-shadow: 0 2px 10px rgba(131, 128, 128, 0.1);
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

        /* Forms - MODIFIÉ POUR RENDRE LE CADRE PLUS GRAND */
        .form-container {
            background: white;
            padding: 70px; /* Augmenté de 60px à 70px */
            border-radius: 15px;
            max-width: 1050px; /* Augmenté de 950px à 1050px */
            margin: 40px auto;
            box-shadow: 0 10px 30px rgba(128, 124, 124, 0.1);
            min-height: 700px; /* Ajouté pour donner plus de hauteur */
        }

        .form-group {
            margin-bottom: 35px; /* Augmenté de 25px à 35px */
        }

        .form-group label {
            display: block;
            margin-bottom: 15px; /* Augmenté de 12px à 15px */
            font-weight: bold;
            font-size: 18px; /* Augmenté de 16px à 18px */
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 18px; /* Augmenté de 14px à 18px */
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 18px; /* Augmenté de 16px à 18px */
        }

        .form-group textarea {
            min-height: 300px; /* Augmenté de 200px à 300px */
            resize: vertical;
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
            padding: 20px; /* Augmenté de 15px à 20px */
            border-radius: 8px; /* Augmenté de 5px à 8px */
            margin-bottom: 30px; /* Augmenté de 20px à 30px */
            font-size: 16px;
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
        
        /* Classes ajoutées */
        .nav-active {
            color: #4361ee;
            font-weight: bold;
        }
        
        .btn {
            display: inline-block;
            text-align: center;
            font-size: 18px; /* Augmenté de 16px à 18px */
            padding: 15px 40px; /* Augmenté de 12px 30px à 15px 40px */
        }
        
        /* TITRE "PUBLIER UNE NOUVELLE IDEE" HORS DU CADRE */
        .page-main-title {
            text-align: center;
            padding: 40px 20px 20px 20px;
            color: #333;
        }
        
        .page-main-title h1 {
            font-size: 42px;
            font-weight: bold;
            color: #4361ee;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .page-main-title .subtitle {
            font-size: 18px;
            color: #666;
            margin-top: 10px;
        }
        
        /* Titre dans le formulaire */
        .form-title {
            font-size: 32px; /* Augmenté de 28px à 32px */
            margin-bottom: 40px; /* Augmenté de 30px à 40px */
            color: #333;
            text-align: center;
            font-weight: bold;
        }
        
        /* Ajout pour améliorer l'espacement du bouton */
        .submit-container {
            margin-top: 50px; /* Augmenté l'espace avant le bouton */
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- UTILISATION EXACTE DE VOTRE STRUCTURE CSS -->
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="../index.php" class="logo">FreelanceHub</a>
                <div class="nav-menu">
                    <a href="dashboard.php">Tableau de bord</a>
                    <a href="ajouter_idee.php" >Publier une idée</a>
                    <a href="mes_idees.php">Mes idées</a>
                </div>
            </nav>
        </div>
    </header>
    
    <!-- TITRE "PUBLIER UNE NOUVELLE IDEE" HORS DU CADRE -->
    <div class="container"><br><br>
        <h1>PUBLIER UNE NOUVELLE IDÉE</h1><br>
        <div class="subtitle">Partagez votre projet avec notre communauté de freelancers</div>
    </div>

    <!-- FORMULAIRE DANS LE CADRE - MAINTENANT PLUS GRAND -->
    <div class="form-container">
        <h2 class="form-title">Remplissez les détails de votre idée</h2>
        
        <?php if($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Titre de l'idée *</label>
                <input type="text" name="titre" required placeholder="Ex: Site web e-commerce" size="50">
            </div>
            
            <div class="form-group">
                <label>Description détaillée *</label>
                <textarea name="description" required placeholder="Décrivez votre projet en détail..."></textarea>
            </div>
            
            <div class="form-group">
                <label>Budget approximatif (MAD)</label>
                <input type="number" name="budget" placeholder="Ex: 1000">
            </div>
            
            <div class="submit-container">
                <button type="submit" class="btn btn-primary">Soumettre l'idée</button>
            </div>
        </form>
    </div>
</body>
</html>