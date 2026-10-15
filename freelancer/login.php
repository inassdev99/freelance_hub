<?php
require_once '../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = securiser($_POST['email']);
    $password = securiser($_POST['password']);
    
    try {
        $stmt = $conn->prepare("SELECT * FROM freelancers WHERE email = ?");
        $stmt->execute([$email]);
        $freelancer = $stmt->fetch();
        
        if ($freelancer) {
            if ($password == $freelancer['password']) {
                $_SESSION['user_id'] = $freelancer['id'];
                $_SESSION['user_type'] = 'freelancer';
                $_SESSION['user_nom'] = $freelancer['nom'];
                $_SESSION['user_email'] = $freelancer['email'];
                
                rediriger('dashboard.php');
            } else {
                $error = "Mot de passe incorrect";
            }
        } else {
            $error = "Email non trouvé";
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
    <title>Connexion Freelancer - FreelanceHub</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="form-container">
        <h2 style="text-align: center; margin-bottom: 30px;">Connexion Freelancer</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Se connecter</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <p>Pas de compte ? <a href="register.php">S'inscrire</a></p>
            <p><a href="../index.php">Retour à l'accueil</a></p>
        </div>
    </div>
</body>
</html>