<?php
require_once '../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = securiser($_POST['username']);
    $password = securiser($_POST['password']);
    
    try {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin) {
            if ($password == $admin['password']) {
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['user_type'] = 'admin';
                $_SESSION['user_nom'] = $admin['username'];
                
                rediriger('dashboard.php');
            } else {
                $error = "Mot de passe incorrect";
            }
        } else {
            $error = "Nom d'utilisateur non trouvé";
        }
    } catch(PDOException $e) {
        $error = "Erreur de connexion";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - FreelanceHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-login {
            background: linear-gradient(135deg, #333 0%, #555 100%);
        }
        
        .admin-login .form-container {
            border-top: 5px solid #4361ee;
        }
    </style>
</head>
<body class="admin-login">
    <div class="form-container">
        <h2 style="text-align: center; margin-bottom: 30px;">Connexion Administrateur</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Nom d'utilisateur</label>
                <input type="text" name="username" required>
            </div>
            
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Se connecter</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <p><a href="../index.php">← Retour à l'accueil</a></p>
        </div>
    </div>
</body>
</html>