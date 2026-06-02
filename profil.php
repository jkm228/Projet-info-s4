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
$adresse = $infos_user['informations']['adresse_livraison'] ?? 'Non renseignée';
$points = $infos_user['fidelite']['points'] ?? 0;
$historique = $infos_user['fidelite']['historique_commandes'] ?? [];
?>

    <main class="profile-page">
        
        <div class="profile-header">
            <h1>Mon Profil</h1>
            <p>Bienvenue, <strong><?php echo htmlspecialchars($infos_user['informations']['prenom']); ?></strong> !</p>
        </div>

        <div class="dashboard-container">
            
            <div class="dashboard-left">
                
                <div class="dashboard-card info-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">
                        <h3 style="margin: 0;">👤 Mes Informations</h3>
                        <button type="button" id="btn-edit-profil" class="btn-submit" style="width: auto; padding: 6px 15px; font-size: 0.9em; background-color: #3498db; margin: 0;">Modifier</button>
                    </div>
                    
                    <form id="form-profil" class="profile-form">
                        <div class="form-group">
                            <label for="nom">Nom :</label>
                            <input type="text" id="profil-nom" value="<?php echo htmlspecialchars($infos_user['informations']['nom']); ?>" class="profile-input" readonly>
                        </div>

                        <div class="form-group">
                            <label for="prenom">Prénom :</label>
                            <input type="text" id="profil-prenom" value="<?php echo htmlspecialchars($infos_user['informations']['prenom']); ?>" class="profile-input" readonly>
                        </div>

                        <div class="form-group">
                            <label for="email">Email :</label>
                            <input type="email" id="profil-email" value="<?php echo htmlspecialchars($infos_user['informations']['email']); ?>" class="profile-input" readonly>
                        </div>

                        <div class="form-group">
                            <label for="tel">Téléphone :</label>
                            <input type="tel" id="profil-tel" value="<?php echo htmlspecialchars($infos_user['informations']['telephone'] ?? ''); ?>" class="profile-input" readonly>
                        </div>

                        <div class="form-group">
                            <label for="adresse">Adresse complète :</label>
                            <input type="text" id="profil-adresse" value="<?php echo htmlspecialchars($adresse); ?>" class="profile-input" readonly>
                        </div>

                        <div id="profil-actions" style="display: none; margin-top: 25px; text-align: center;">
                            <button type="submit" id="btn-save-profil" class="btn-submit" style="background-color: #27ae60; padding: 12px 25px; width: 100%;">Enregistrer les modifications</button>
                            <p id="profil-msg" style="margin-top: 10px; font-weight: bold;"></p>
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
                            foreach($commandes as $index_commande => $cmd): 
                                $date_affichage = $cmd['date_passage'] ?? $cmd['date'] ?? 'Date inconnue';
                                $statut = $cmd['statut'] ?? 'Terminée'; // Par défaut
                            ?>
                                <div style="border-left: 4px solid #e74c3c; background: #f9f9f9; padding: 15px; margin-bottom: 15px; border-radius: 0 5px 5px 0; text-align: left;">
                                    <div style="display: flex; justify-content: space-between; font-weight: bold;">
                                        <span>📅 <?php echo $date_affichage; ?></span>
                                        <span style="color: #e74c3c;"><?php echo number_format($cmd['total'], 2); ?> €</span>
                                    </div>
                                    
                                    <?php if(isset($cmd['type'])): ?>
                                        <div style="font-size: 0.85em; color: #2980b9; margin-top: 5px; font-weight: bold; display: flex; justify-content: space-between; align-items: center;">
                                            <span>
                                                <?php echo ($cmd['type'] == 'Livraison' ? '🛵 ' : '🛍️ ') . htmlspecialchars($cmd['type']); ?> 
                                                - Prévu : <?php echo htmlspecialchars($cmd['date_prevue']); ?>
                                                <br>
                                                <span style="color: #f39c12;">Statut : <?php echo htmlspecialchars($statut); ?></span>
                                            </span>
                                            
                                            <div style="text-align: right;">
                                                <?php $vrai_index = count($historique) - 1 - $index_commande; ?>
                                                
                                                <?php if($statut === 'À préparer'): ?>
                                                    <a href="modifier_commande.php?id_cmd=<?php echo $vrai_index; ?>" class="btn-submit" style="width: auto; padding: 6px 12px; font-size: 0.85em; background-color: #f39c12; text-decoration: none;">✏️ Modifier</a>
                                                
                                                <?php elseif($statut === 'En préparation'): ?>
                                                    <span style="display: inline-block; background: #e67e22; color: white; padding: 5px 10px; border-radius: 4px; font-weight: bold; font-size: 0.9em;">
                                                        👨‍🍳 En cuisine...
                                                    </span>

                                                <?php elseif($statut === 'Prête'): ?>
                                                    <span style="display: inline-block; background: #8e44ad; color: white; padding: 5px 10px; border-radius: 4px; font-weight: bold; font-size: 0.9em;">
                                                        ✅ Préparation terminée
                                                    </span>
                                                
                                                <?php elseif ($statut === 'À récupérer'): ?>
                                                    <span style="display: inline-block; background: #27ae60; color: white; padding: 5px 10px; border-radius: 4px; font-weight: bold; box-shadow: 0 0 10px rgba(39, 174, 96, 0.5);">
                                                        🛍️ Prête ! Venez la récupérer.
                                                    </span>
                                                    
                                                <?php elseif ($statut === 'En cours de livraison'): ?>
                                                    <span style="display: inline-block; background: #3498db; color: white; padding: 5px 10px; border-radius: 4px; font-weight: bold; font-size: 0.9em;">
                                                        🛵 Le livreur est en route !
                                                    </span>

                                                <?php elseif($statut === 'Livrée' || $statut === 'Terminée'): ?>
                                                    
                                                    <?php if(isset($cmd['note'])): ?>
                                                        <span style="color: #f1c40f; font-size: 1.2em;" title="Votre note">
                                                            <?php echo str_repeat('★', $cmd['note']) . str_repeat('☆', 5 - $cmd['note']); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <button onclick="noterCommande(<?php echo $vrai_index; ?>)" class="btn-submit" style="width: auto; padding: 6px 12px; font-size: 0.85em; background-color: #9b59b6; color: white; border: none; cursor: pointer;">⭐ Noter</button>
                                                    <?php endif; ?>
                                                    
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <p style="font-size: 0.9em; color: #555; margin-top: 5px;">
                                        <?php echo htmlspecialchars(implode(', ', $cmd['articles'])); ?>
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