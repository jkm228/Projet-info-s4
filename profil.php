<?php 
$page_title = "Africa United - Mon Profil"; 
include 'includes/header.php'; 

// 🔒 SÉCURITÉ : Si l'utilisateur n'est pas connecté, on le renvoie à la connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

// 1. On récupère les données de tous les utilisateurs
$json_data = file_get_contents('data/utilisateurs.json');
$utilisateurs = json_decode($json_data, true);

// 2. On cherche l'utilisateur actuel grâce à son ID stocké en session
$infos_user = null;
foreach ($utilisateurs as $user) {
    if ($user['id'] === $_SESSION['user_id']) {
        $infos_user = $user;
        break;
    }
}

// Si par erreur on ne trouve pas l'utilisateur, on le déconnecte
if (!$infos_user) {
    header("Location: connexion.php");
    exit();
}

// --- 🛡️ PROTECTION CONTRE LES BUGS ---
// On prépare des variables sécurisées au cas où l'utilisateur (comme l'Admin) n'aurait pas ces infos.
// L'opérateur "??" signifie : "Prend cette valeur, OU BIEN prend ce qu'il y a à droite si elle n'existe pas".
$adresse = $infos_user['informations']['adresse_livraison'] ?? 'Non renseignée';
$points = $infos_user['fidelite']['points'] ?? 0;
$historique = $infos_user['fidelite']['historique_commandes'] ?? [];
?>

    <main class="profile-page">
        
        <div class="profile-header">
            <h1>Mon Profil</h1>
            <p>Bienvenue, <strong><?php echo $infos_user['informations']['prenom']; ?></strong> !</p>
        </div>

        <div class="dashboard-container">
            
            <div class="dashboard-left">
                
                <div class="dashboard-card info-card">
                    <h3>👤 Mes Informations Personnelles</h3>
                    
                    <form class="profile-form">
                        <div class="form-group">
                            <label for="nom">Nom :</label>
                            <div class="input-wrapper">
                                <input type="text" value="<?php echo $infos_user['informations']['nom']; ?>" class="profile-input" readonly>
                                <span class="edit-icon">✏️</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="prenom">Prénom :</label>
                            <div class="input-wrapper">
                                <input type="text" value="<?php echo $infos_user['informations']['prenom']; ?>" class="profile-input" readonly>
                                <span class="edit-icon">✏️</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email :</label>
                            <div class="input-wrapper">
                                <input type="email" value="<?php echo $infos_user['informations']['email']; ?>" class="profile-input" readonly>
                                <span class="edit-icon">✏️</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="tel">Téléphone :</label>
                            <div class="input-wrapper">
                                <input type="tel" value="<?php echo $infos_user['informations']['telephone'] ?? 'Non renseigné'; ?>" class="profile-input" readonly>
                                <span class="edit-icon">✏️</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="adresse">Adresse complète :</label>
                            <div class="input-wrapper">
                                <input type="text" value="<?php echo $adresse; ?>" class="profile-input" readonly>
                                <span class="edit-icon">✏️</span>
                            </div>
                        </div>
                    </form>
                </div>

            </div>

            <div class="dashboard-right">
                
                <div class="dashboard-card loyalty-card">
                    <h3>✈️ Mon Solde Fidélité</h3>
                    
                    <div class="loyalty-center">
                        <div class="zero-points-circle">
                            <span class="big-number"><?php echo $points; ?></span>
                            <span class="points-label">Points</span>
                        </div>
                        
                        <?php if($points == 0): ?>
                            <p class="loyalty-prompt">Commencez à commander pour cumuler des Points !</p>
                        <?php endif; ?>
                        
                        <div class="progress-container">
                            <div class="progress-bar" style="width: <?php echo min($points, 100); ?>%;"></div>
                        </div>
                        <p class="progress-text">Prochaine récompense à <strong>100 points</strong></p>
                    </div>
                </div>

                <div class="dashboard-card history-card">
                    <h3>📦 Historique de Commandes</h3>
                    
                    <?php if(empty($historique)): ?>
                        <div class="history-empty-state">
                            <p>Vous n'avez pas encore passé de commande.</p>
                            <a href="presentation.php" class="btn-submit btn-discover-menu">Découvrir la carte</a>
                        </div>
                    <?php else: ?>
                        <div class="orders-list">
                            <?php 
                            $commandes = array_reverse($historique); 
                            foreach($commandes as $cmd): 
                                // MAGIE : On cherche 'date_passage' (nouveau format), sinon 'date' (ancien format)
                                $date_affichage = $cmd['date_passage'] ?? $cmd['date'] ?? 'Date inconnue';
                            ?>
                                <div style="border-left: 4px solid #e74c3c; background: #f9f9f9; padding: 15px; margin-bottom: 15px; border-radius: 0 5px 5px 0; text-align: left;">
                                    <div style="display: flex; justify-content: space-between; font-weight: bold;">
                                        <span>📅 <?php echo $date_affichage; ?></span>
                                        <span style="color: #e74c3c;"><?php echo number_format($cmd['total'], 2); ?> €</span>
                                    </div>
                                    
                                    <?php if(isset($cmd['type'])): ?>
                                        <div style="font-size: 0.85em; color: #2980b9; margin-top: 5px; font-weight: bold;">
                                            <?php echo ($cmd['type'] == 'Livraison' ? '🛵 ' : '🛍️ ') . $cmd['type']; ?> 
                                            - Prévu : <?php echo $cmd['date_prevue']; ?>
                                        </div>
                                    <?php endif; ?>

                                    <p style="font-size: 0.9em; color: #555; margin-top: 5px;">
                                        <?php echo implode(', ', $cmd['articles']); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>

<?php 
include 'includes/footer.php'; 
?>