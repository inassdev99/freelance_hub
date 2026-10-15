<?php
require_once '../config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = securiser($_POST['nom']);
    $email = securiser($_POST['email']);
    $password = securiser($_POST['password']);
    $confirm_password = securiser($_POST['confirm_password']);
    
    if (empty($nom) || empty($email) || empty($password)) {
        $error = "Tous les champs sont obligatoires";
    } elseif ($password != $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    } else {
        try {
            $stmt = $conn->prepare("SELECT id FROM clients WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Cet email existe déjà";
            } else {
                $stmt = $conn->prepare("INSERT INTO clients (nom, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$nom, $email, $password]);
                
                $id = $conn->lastInsertId();
                $_SESSION['user_id'] = $id;
                $_SESSION['user_type'] = 'client';
                $_SESSION['user_nom'] = $nom;
                $_SESSION['user_email'] = $email;
                
                $success = "Inscription réussie !";
                echo "<script>setTimeout(function(){ window.location.href = 'dashboard.php'; }, 2000);</script>";
            }
        } catch(PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Client - FreelanceHub</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="form-container">
        <h2 style="text-align: center; margin-bottom: 30px;">Inscription Client</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Nom</label>
                <input type="text" name="nom" required>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label>Confirmer mot de passe</label>
                <input type="password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">S'inscrire</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
            <p><a href="../index.php">Retour à l'accueil</a></p>
        </div>
    </div>
</body>
</html>