<?php
require_once '../config.php';

// Vérifier si admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$reclamation_id = intval($_GET['id'] ?? 0);
if ($reclamation_id <= 0) {
    header('Location: reclamations.php');
    exit();
}

$error = '';
$success = '';

// Requête SELECT originale
$select_sql = "SELECT r.*, 
               CASE 
                   WHEN r.user_type = 'freelancer' THEN f.nom
                   WHEN r.user_type = 'client' THEN c.nom
               END as nom_utilisateur,
               CASE 
                   WHEN r.user_type = 'freelancer' THEN f.email
                   WHEN r.user_type = 'client' THEN c.email
               END as email_utilisateur
        FROM reclamations r
        LEFT JOIN freelancers f ON r.user_id = f.id AND r.user_type = 'freelancer'
        LEFT JOIN clients c ON r.user_id = c.id AND r.user_type = 'client'
        WHERE r.id = ?";

// Récupérere la réclamation
$stmt = $conn->prepare($select_sql);
$stmt->execute([$reclamation_id]);
$reclamation = $stmt->fetch();

if (!$reclamation) {
    header('Location: reclamations.php');
    exit();
}

// traitement des actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['changer_statut'])) {
    $nouveau_statut = $_POST['statut'];
    $reponse_admin = $_POST['reponse'] ?? '';
    
    try {
        // mis à jour de statut
        $update_sql = "UPDATE reclamations SET statut = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->execute([$nouveau_statut, $reclamation_id]);
        
        //  vérification si la table reponses_reclamation existe
        try {
            $test = $conn->query("SELECT 1 FROM reponses_reclamation LIMIT 1");
            $table_exists = true;
        } catch(Exception $e) {
            $table_exists = false;
        }
        
        //  si la table existe et qu'il y a une réponse, l'enregistrer
        if ($table_exists && !empty($reponse_admin)) {
            $response_sql = "INSERT INTO reponses_reclamation (reclamation_id, admin_id, reponse) 
                            VALUES (?, ?, ?)";
            $stmt = $conn->prepare($response_sql);
            $stmt->execute([$reclamation_id, $_SESSION['user_id'], $reponse_admin]);
        }
        
        // Sinon, mis à jour directement dans reclamations si les colonnes existent
        else if (!empty($reponse_admin)) {
            try {
                // verification si les colonnes existent
                $test = $conn->query("SELECT reponse_admin FROM reclamations LIMIT 1");
                
                // mis à jour
                $update_response_sql = "UPDATE reclamations 
                                        SET reponse_admin = ?, 
                                            admin_id = ?, 
                                            date_reponse = NOW() 
                                        WHERE id = ?";
                $stmt = $conn->prepare($update_response_sql);
                $stmt->execute([$reponse_admin, $_SESSION['user_id'], $reclamation_id]);
            } catch(Exception $e) {
                // colonnes n'existent pas on ignore
            }
        }
        
        $success = "Statut mis à jour avec succès !";
        
        // recharge la réclamation avec la requête SELECT originale
        $stmt = $conn->prepare($select_sql);
        $stmt->execute([$reclamation_id]);
        $reclamation = $stmt->fetch();
        
    } catch(Exception $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion Réclamation #<?php echo $reclamation_id; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .container { max-width: 1000px; margin: auto; padding: 20px; }
        
        .menu-admin {
            background: #2c3e50;
            color: white;
            padding: 15px 0;
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
        }
        .menu-admin a:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #495057;
        }
        select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: Arial, sans-serif;
        }
        textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: bold;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .badge-ouverte { background: #fff3cd; color: #856404; }
        .badge-en_cours { background: #cce5ff; color: #004085; }
        .badge-resolue { background: #d4edda; color: #155724; }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
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
                <a href="reclamations.php">Reclamations</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h1 style="color: #2c3e50; margin: 0;">
                     Gestion réclamation #<?php echo $reclamation_id; ?>
                </h1>
                <p style="color: #6c757d; margin: 5px 0 0 0;">
                    <?php echo htmlspecialchars($reclamation['titre']); ?>
                </p>
            </div>
            <a href="reclamations.php" class="btn btn-secondary">← Retour</a>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- informations -->
        <div class="card">
            <h3 style="margin-top: 0; color: #495057;">📋 Informations</h3>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Utilisateur :</strong><br>
                    <?php echo htmlspecialchars($reclamation['nom_utilisateur'] ?? 'Utilisateur'); ?>
                    <br><small style="color: #6c757d;">(<?php echo $reclamation['user_type']; ?>)</small>
                </div>
                <div class="info-item">
                    <strong>Email :</strong><br>
                    <?php echo htmlspecialchars($reclamation['email_utilisateur'] ?? 'Non disponible'); ?>
                </div>
                <div class="info-item">
                    <strong>Date :</strong><br>
                    <?php echo date('d/m/Y H:i', strtotime($reclamation['date_creation'])); ?>
                </div>
                <div class="info-item">
                    <strong>Statut actuel :</strong><br>
                    <span class="badge badge-<?php echo $reclamation['statut']; ?>" style="margin-top: 5px;">
                        <?php echo ucfirst($reclamation['statut']); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Réclamation -->
        <div class="card">
            <h3 style="margin-top: 0; color: #495057;">📄 Réclamation</h3>
            <div style="margin-bottom: 20px;">
                <strong>Titre :</strong>
                <p style="margin: 10px 0 20px 0; font-size: 18px; color: #212529;">
                    <?php echo htmlspecialchars($reclamation['titre']); ?>
                </p>
            </div>
            
            <div>
                <strong>Description :</strong>
                <div style="background: #f8f9fa; padding: 20px; border-radius: 6px; margin-top: 10px;">
                    <?php echo nl2br(htmlspecialchars($reclamation['description'])); ?>
                </div>
            </div>
        </div>
        
        <!-- gestion Admin -->
        <div class="card">
            <h3 style="margin-top: 0; color: #495057;">⚙️ Gestion Admin</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Changer le statut :</label>
                    <select name="statut">
                        <option value="ouverte" <?php if($reclamation['statut']=='ouverte') echo 'selected'; ?>>Ouverte</option>
                        <option value="en_cours" <?php if($reclamation['statut']=='en_cours') echo 'selected'; ?>>En cours</option>
                        <option value="resolue" <?php if($reclamation['statut']=='resolue') echo 'selected'; ?>>Résolue</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Réponse admin :</label>
                    <textarea name="reponse" placeholder="Écrivez votre réponse à l'utilisateur..."></textarea>
                </div>
                
                <button type="submit" name="changer_statut" class="btn btn-primary">
                    💾 Enregistrer les modifications
                </button>
            </form>
        </div>
        
        <!-- les actions rapides -->
        <div class="card">
            <h3 style="margin-top: 0; color: #495057;">🚀 Actions rapides</h3>
            <div style="display: flex; gap: 15px;">
                <?php if(!empty($reclamation['email_utilisateur'])): ?>
                    <a href="mailto:<?php echo htmlspecialchars($reclamation['email_utilisateur']); ?>" 
                       class="btn" style="background: #17a2b8; color: white;">
                       📧 Contacter par email
                    </a>
                <?php endif; ?>
                <a href="reclamations.php" class="btn btn-secondary">
                    ← Retour aux réclamations
                </a>
            </div>
        </div>
    </div>
</body>
</html>