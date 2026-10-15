<?php
require_once '../config.php';

if (!estConnecte() || !isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'freelancer') {
    rediriger('login.php');
}

$idee_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($idee_id == 0) {
    rediriger('idees.php');
}

$error = '';
$success = '';
$etape_negociation = 'initial'; // initial, negociation, accepte, refuse
$historique_negociations = []; // Initialiser la variable pour éviter l'erreur
$derniere_offre = null; // Initialiser la variable
$proposition = null; // Initialiser la variable

try {
    // Récupérer l'idée
    $stmt = $conn->prepare("SELECT i.*, c.nom as client_nom, c.email as client_email, c.id as client_id 
                           FROM idees i 
                           JOIN clients c ON i.client_id = c.id 
                           WHERE i.id = ?");
    $stmt->execute([$idee_id]);
    $idee = $stmt->fetch();
    
    if (!$idee) {
        $error = "Cette idée n'est plus disponible.";
        rediriger('idees.php');
    }
    
    $freelancer_id = $_SESSION['user_id'];
    
    // Récupérer ou créer la proposition
    $stmt = $conn->prepare("SELECT * FROM propositions WHERE idee_id = ? AND freelancer_id = ?");
    $stmt->execute([$idee_id, $freelancer_id]);
    $proposition = $stmt->fetch();
    
    // Si pas de proposition, en créer une initiale
    if (!$proposition) {
        $stmt = $conn->prepare("INSERT INTO propositions (idee_id, freelancer_id, statut,date_creation) 
                               VALUES (?, ?, 'en_attente', NOW())");
        $stmt->execute([$idee_id, $freelancer_id]);
        $proposition_id = $conn->lastInsertId();
        
        $stmt = $conn->prepare("SELECT * FROM propositions WHERE id = ?");
        $stmt->execute([$proposition_id]);
        $proposition = $stmt->fetch();
    }
    
    // Récupérer l'historique des négociations (seulement si la proposition existe)
    if ($proposition && isset($proposition['id'])) {
        $stmt = $conn->prepare("SELECT * FROM negociations 
                               WHERE proposition_id = ? 
                               ORDER BY date_creation ASC");
        $stmt->execute([$proposition['id']]);
        $historique_negociations = $stmt->fetchAll();
        
        // Récupérer la dernière offre en attente
        foreach (array_reverse($historique_negociations) as $neg) {
            if ($neg['statut'] == 'en_attente') {
                $derniere_offre = $neg;
                break;
            }
        }
    }
    
    // Déterminer l'étape actuelle
    if ($proposition && $proposition['statut'] == 'accepte') {
        $etape_negociation = 'accepte';
    } elseif ($proposition && $proposition['statut'] == 'refuse') {
        $etape_negociation = 'refuse';
    } elseif (count($historique_negociations) > 0) {
        $etape_negociation = 'negociation';
    }
    
    // Traitement du formulaire
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'proposer_prix':
                $prix_propose = floatval($_POST['prix_propose']);
                $message = securiser($_POST['message']);
                $delai_jours = intval($_POST['delai_jours']);
                
                if ($prix_propose <= 0) {
                    $error = "Veuillez proposer un prix valide.";
                } elseif ($delai_jours <= 0) {
                    $error = "Veuillez indiquer un délai valide.";
                } else {
                    // Vérifier que la proposition existe
                    if (!$proposition) {
                        $error = "Erreur: proposition non trouvée.";
                        break;
                    }
                    
                    // Insérer la négociation
                    $stmt = $conn->prepare("INSERT INTO negociations 
                                          (proposition_id, type, prix_propose, message, delai_jours, statut, date_creation) 
                                          VALUES (?, 'offre_freelancer', ?, ?, ?, 'en_attente', NOW())");
                    $stmt->execute([$proposition['id'], $prix_propose, $message, $delai_jours]);
                    
                    // Mettre à jour le statut de l'idée
                    $stmt = $conn->prepare("UPDATE idees SET statut = 'en_negociation' WHERE id = ?");
                    $stmt->execute([$idee_id]);
                    
                    $success = "Votre offre a été envoyée au client !";
                    rediriger("postuler.php?id=$idee_id");
                }
                break;
                
            case 'repondre_offre':
                $reponse = $_POST['reponse'];
                $negociation_id = intval($_POST['negociation_id']);
                
                if (!$derniere_offre) {
                    $error = "Aucune offre en attente de réponse.";
                    break;
                }
                
                if ($reponse == 'accepter') {
                    // Accepter l'offre du client
                    $stmt = $conn->prepare("UPDATE negociations SET statut = 'accepte' WHERE id = ?");
                    $stmt->execute([$negociation_id]);
                    
                    $stmt = $conn->prepare("UPDATE propositions SET statut = 'accepte', prix_final = ? WHERE id = ?");
                    $stmt->execute([$derniere_offre['prix_propose'], $proposition['id']]);
                    
                    $stmt = $conn->prepare("UPDATE idees SET statut = 'attribue' WHERE id = ?");
                    $stmt->execute([$idee_id]);
                    
                    $success = "Vous avez accepté l'offre du client ! Le projet peut commencer.";
                    $etape_negociation = 'accepte';
                } elseif ($reponse == 'refuser') {
                    // Refuser l'offre
                    $stmt = $conn->prepare("UPDATE negociations SET statut = 'refuse' WHERE id = ?");
                    $stmt->execute([$negociation_id]);
                    
                    $stmt = $conn->prepare("UPDATE propositions SET statut = 'refuse' WHERE id = ?");
                    $stmt->execute([$proposition['id']]);
                    
                    $error = "Vous avez refusé l'offre du client.";
                    $etape_negociation = 'refuse';
                } elseif ($reponse == 'contre_offre') {
                    $prix_propose = floatval($_POST['nouveau_prix']);
                    $message = securiser($_POST['message_contre_offre']);
                    
                    // Marquer l'offre précédente comme traitée
                    $stmt = $conn->prepare("UPDATE negociations SET statut = 'traite' WHERE id = ?");
                    $stmt->execute([$negociation_id]);
                    
                    // Créer une contre-offre
                    $stmt = $conn->prepare("INSERT INTO negociations 
                                          (proposition_id, type, prix_propose, message, statut, date_creation) 
                                          VALUES (?, 'contre_offre_freelancer', ?, ?, 'en_attente', NOW())");
                    $stmt->execute([$proposition['id'], $prix_propose, $message]);
                    
                    $success = "Votre contre-offre a été envoyée au client !";
                }
                break;
                
            case 'annuler':
                if ($proposition) {
                    $stmt = $conn->prepare("UPDATE propositions SET statut = 'annule' WHERE id = ?");
                    $stmt->execute([$proposition['id']]);
                    $success = "Négociation annulée.";
                    rediriger('idees.php');
                }
                break;
        }
    }
    
} catch(PDOException $e) {
    $error = "Erreur : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Négocier le projet - FreelanceHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .negotiation-container {
            max-width: 1000px;
            margin: 20px auto; /* Réduit de 30px à 20px pour monter le contenu */
        }
        
        /* MODIFICATION : Titre principal plus haut SANS ligne */
        .page-title {
            margin-top: 10px; /* Réduit encore plus la marge supérieure */
            margin-bottom: 30px;
        }
        
        .project-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-left: 5px solid #4361ee;
        }
        
        .negotiation-timeline {
            position: relative;
            padding: 20px 0;
            margin: 30px 0;
        }
        
        .timeline-item {
            display: flex;
            margin-bottom: 25px;
            position: relative;
        }
        
        .timeline-item:before {
            content: '';
            position: absolute;
            left: 30px;
            top: 0;
            bottom: -25px;
            width: 2px;
            background: #e0e0e0;
        }
        
        .timeline-item:last-child:before {
            display: none;
        }
        
        .timeline-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            z-index: 1;
            position: relative;
        }
        
        .client-icon {
            background: #7209b7;
            color: white;
        }
        
        .freelancer-icon {
            background: #4361ee;
            color: white;
        }
        
        .timeline-content {
            flex: 1;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }
        
        .price-badge {
            display: inline-block;
            padding: 8px 15px;
            background: white;
            border-radius: 20px;
            font-weight: bold;
            margin: 10px 0;
            border: 2px solid;
        }
        
        .client-price {
            color: #7209b7;
            border-color: #7209b7;
        }
        
        .freelancer-price {
            color: #4361ee;
            border-color: #4361ee;
        }
        
        .negotiation-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-top: 30px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .offer-response {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            margin-left: 10px;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-accepted {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .btn-radio {
            display: inline-block;
            padding: 10px 20px;
            border: 2px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-radio input[type="radio"] {
            display: none;
        }
        
        .btn-radio input[type="radio"]:checked + .fa {
            color: #4361ee;
        }
        
        .btn-radio:hover {
            border-color: #4361ee;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="../index.php" class="logo">FreelanceHub</a>
                <div class="nav-menu">
                    <a href="dashboard.php">Tableau de bord</a>
                    <a href="idees.php">Idées</a>
                    <a href="projets.php">Projets</a>
                </div>
            </nav>
        </div>
    </header>

    <!-- MODIFICATION : Le titre est maintenant plus haut avec moins d'espace au-dessus -->
    <main class="container" style="margin-top: 40px;"> <!-- Réduit de 80px à 40px -->
        <div class="negotiation-container">
            <!-- Titre sans ligne en dessous -->
            <div class="page-title">
                <h1><i class="fas fa-handshake"></i> Négociation du projet</h1>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div><br><br>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div><br><br>
            <?php endif; ?>
            
            <!-- Carte du projet -->
            <div class="project-card">
                <h2><?php echo htmlspecialchars($idee['titre']); ?></h2>
                <div class="project-meta">
                    <span><i class="fas fa-user"></i> Client : <?php echo htmlspecialchars($idee['client_nom']); ?></span>
                    <span style="margin-left: 20px;">
                        <i class="fas fa-euro-sign"></i> Budget initial : 
                        <strong><?php echo $idee['budget'] ? htmlspecialchars($idee['budget']) . ' MAD' : 'Non spécifié'; ?></strong>
                    </span>
                </div><br>
                <p><strong>Description :</strong></p>
                <p><?php echo nl2br(htmlspecialchars($idee['description'])); ?></p>
                
                <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                    <strong>Statut de la négociation :</strong>
                    <?php if ($etape_negociation == 'accepte'): ?>
                        <span class="status-badge status-accepted">
                            <i class="fas fa-check-circle"></i> Projet accepté
                        </span>
                    <?php elseif ($etape_negociation == 'refuse'): ?>
                        <span class="status-badge status-rejected">
                            <i class="fas fa-times-circle"></i> Négociation refusée
                        </span>
                    <?php elseif ($etape_negociation == 'negociation'): ?>
                        <span class="status-badge status-pending">
                            <i class="fas fa-clock"></i> En négociation
                        </span>
                    <?php else: ?>
                        <span class="status-badge status-pending">
                            <i class="fas fa-clock"></i> En attente de proposition
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Historique des négociations -->
            <?php if (is_array($historique_negociations) && count($historique_negociations) > 0): ?>
            <h2><i class="fas fa-history"></i> Historique des négociations</h2>
            <div class="negotiation-timeline">
                <?php foreach ($historique_negociations as $neg): ?>
                <div class="timeline-item">
                    <div class="timeline-icon <?php echo strpos($neg['type'], 'client') !== false ? 'client-icon' : 'freelancer-icon'; ?>">
                        <?php if (strpos($neg['type'], 'client') !== false): ?>
                            <i class="fas fa-user-tie fa-lg"></i>
                        <?php else: ?>
                            <i class="fas fa-code fa-lg"></i>
                        <?php endif; ?>
                    </div>
                    <div class="timeline-content">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <strong>
                                <?php echo strpos($neg['type'], 'client') !== false ? 'Client' : 'Vous'; ?>
                            </strong>
                            <small style="color: #666;"><?php echo date('d/m/Y H:i', strtotime($neg['date_creation'])); ?></small>
                        </div>
                        
                        <div class="price-badge <?php echo strpos($neg['type'], 'client') !== false ? 'client-price' : 'freelancer-price'; ?>">
                            <i class="fas fa-euro-sign"></i> <?php echo htmlspecialchars($neg['prix_propose']); ?> MAD
                            <?php if (isset($neg['delai_jours']) && $neg['delai_jours']): ?>
                                <span style="font-size: 12px; margin-left: 10px;">
                                    <i class="far fa-clock"></i> <?php echo htmlspecialchars($neg['delai_jours']); ?> jours
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($neg['message']): ?>
                            <p style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #eee;">
                                <?php echo nl2br(htmlspecialchars($neg['message'])); ?>
                            </p>
                        <?php endif; ?>
                        
                        <div style="margin-top: 10px;">
                            <?php if ($neg['statut'] == 'accepte'): ?>
                                <span class="status-badge status-accepted">Accepté</span>
                            <?php elseif ($neg['statut'] == 'refuse'): ?>
                                <span class="status-badge status-rejected">Refusé</span>
                            <?php elseif ($neg['statut'] == 'en_attente'): ?>
                                <span class="status-badge status-pending">En attente</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- Formulaire selon l'étape -->
            <?php if ($etape_negociation == 'accepte'): ?>
                <div class="alert alert-success" style="text-align: center; padding: 30px;">
                    <h3><i class="fas fa-check-circle"></i> Projet accepté !</h3>
                    <p>Le projet a été accepté au prix de <strong><?php echo isset($proposition['prix_final']) ? htmlspecialchars($proposition['prix_final']) : '0'; ?> MAD</strong></p><br>
                    <p>Vous pouvez maintenant commencer à travailler sur le projet.</p><br>
                    <a href="projet.php?id=<?php echo $idee_id; ?>" class="btn btn-primary">
                        <i class="fas fa-rocket"></i> Accéder au projet
                    </a>
                </div>
                
            <?php elseif ($etape_negociation == 'refuse'): ?>
                <div class="alert alert-error" style="text-align: center; padding: 30px;">
                    <h3><i class="fas fa-times-circle"></i> Négociation terminée</h3>
                    <p>Cette négociation a été refusée.</p><br>
                    <a href="idees.php" class="btn btn-primary">Voir d'autres idées</a>
                </div>
                
            <?php elseif ($derniere_offre && isset($derniere_offre['type']) && $derniere_offre['type'] == 'offre_client'): ?>
                <!-- Répondre à une offre du client -->
                <div class="negotiation-form">
                    <h3><i class="fas fa-reply"></i> Répondre à l'offre du client</h3>
                    
                    <div class="offer-response">
                        <h4>Offre du client :</h4>
                        <div class="price-badge client-price">
                            <i class="fas fa-euro-sign"></i> <?php echo htmlspecialchars($derniere_offre['prix_propose']); ?> MAD
                        </div>
                        <?php if ($derniere_offre['message']): ?>
                            <p><strong>Message :</strong> <?php echo nl2br(htmlspecialchars($derniere_offre['message'])); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="repondre_offre">
                        <input type="hidden" name="negociation_id" value="<?php echo htmlspecialchars($derniere_offre['id']); ?>">
                        
                        <div class="form-group">
                            <label>Votre réponse :</label>
                            <div style="display: flex; gap: 15px; margin: 15px 0; flex-wrap: wrap;">
                                <label class="btn-radio">
                                    <input type="radio" name="reponse" value="accepter" onchange="toggleCounterOffer(false)">
                                    <i class="fas fa-check"></i> Accepter l'offre
                                </label>
                                <label class="btn-radio">
                                    <input type="radio" name="reponse" value="refuser" onchange="toggleCounterOffer(false)">
                                    <i class="fas fa-times"></i> Refuser
                                </label>
                                <label class="btn-radio">
                                    <input type="radio" name="reponse" value="contre_offre" onchange="toggleCounterOffer(true)" checked>
                                    <i class="fas fa-exchange-alt"></i> Faire une contre-offre
                                </label>
                            </div>
                        </div>
                        
                        <div id="counter-offer-section" style="margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
                            <h4>Votre contre-offre</h4>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Nouveau prix (MAD) *</label>
                                    <input type="number" name="nouveau_prix" 
                                           step="0.01" min="0.01" required 
                                           value="<?php echo isset($derniere_offre['prix_propose']) ? htmlspecialchars($derniere_offre['prix_propose'] * 1.1) : ''; ?>">
                                    <small>Recommandé : 10-20% de plus que l'offre actuelle</small>
                                </div>
                                <div class="form-group">
                                    <label>Délai (jours) *</label>
                                    <input type="number" name="delai_jours" min="1" value="14" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Message au client</label>
                                <textarea name="message_contre_offre" rows="4" required 
                                          placeholder="Expliquez pourquoi vous proposez ce prix..."></textarea>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 15px; margin-top: 30px; flex-wrap: wrap;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Envoyer la réponse
                            </button>
                            <button type="submit" name="action" value="annuler" class="btn btn-outline">
                                Annuler la négociation
                            </button>
                        </div>
                    </form>
                </div>
                
            <?php else: ?>
                <!-- Formulaire de proposition initiale ou nouvelle offre -->
                <div class="negotiation-form">
                    <h3>
                        <?php echo is_array($historique_negociations) && count($historique_negociations) > 0 ? 'Faire une nouvelle offre' : 'Faire votre première offre'; ?>
                    </h3>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="proposer_prix">
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Prix proposé (MAD) *</label>
                                    <input type="number" name="prix_propose" 
                                       step="0.01" min="0.01" required 
                                       value="<?php echo $idee['budget'] ? htmlspecialchars($idee['budget']) : '500'; ?>">
                                <small>
                                    Budget client : <?php echo $idee['budget'] ? htmlspecialchars($idee['budget']) . ' MAD' : 'Non spécifié'; ?>
                                </small>
                            </div>
                            <div class="form-group">
                                <label>Délai de livraison (jours) *</label>
                                <input type="number" name="delai_jours" min="1" value="14" required>
                                <small>Durée estimée pour réaliser le projet</small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Votre message *</label>
                            <textarea name="message" rows="6" required 
                                      placeholder="Présentez votre approche, votre expérience, et justifiez votre prix..."></textarea>
                            <small>Conseil : Soyez clair sur ce qui est inclus dans votre prix.</small>
                        </div>
                        
                        <div style="display: flex; gap: 15px; margin-top: 30px; flex-wrap: wrap;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> 
                                <?php echo is_array($historique_negociations) && count($historique_negociations) > 0 ? 'Envoyer la nouvelle offre' : 'Envoyer la proposition'; ?>
                            </button>
                            <a href="idees.php" class="btn btn-outline">Annuler</a>
                            
                            <?php if (is_array($historique_negociations) && count($historique_negociations) > 0): ?>
                                <button type="submit" name="action" value="annuler" class="btn btn-outline" style="margin-left: auto;">
                                    Abandonner la négociation
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <script>
        function toggleCounterOffer(show) {
            const section = document.getElementById('counter-offer-section');
            if (section) {
                section.style.display = show ? 'block' : 'none';
                
                // Rendre les champs obligatoires si visible
                const inputs = section.querySelectorAll('input, textarea');
                inputs.forEach(input => {
                    input.required = show;
                });
            }
        }
        
        // Initialiser l'état de la contre-offre
        document.addEventListener('DOMContentLoaded', function() {
            const contreOffreRadio = document.querySelector('input[value="contre_offre"]');
            if (contreOffreRadio && contreOffreRadio.checked) {
                toggleCounterOffer(true);
            } else {
                toggleCounterOffer(false);
            }
            
            // Gérer les changements de radio
            const radios = document.querySelectorAll('input[name="reponse"]');
            radios.forEach(radio => {
                radio.addEventListener('change', function() {
                    toggleCounterOffer(this.value === 'contre_offre');
                });
            });
        });
    </script>
</body>
</html>