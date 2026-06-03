<?php 
$page_title = "Africa United - Mon Profil"; 
include 'includes/header.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$json_data = file_get_contents('data/utilisateurs.json');
$utilisateurs = json_decode($json_data, true);

$infos_user = null;
foreach ($utilisateurs as $user) {
    if ($user['id'] === $_SESSION['user_id']) {
        $infos_user = $user;
        break;
    }
}

if (!$infos_user) {
    header("Location: connexion.php");
    exit();
}

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
                    <div class="profil-info-header">
                        <h3 class="margin-0">👤 Mes Informations</h3>
                        <button type="button" id="btn-edit-profil" class="btn-submit btn-edit-small">Modifier</button>
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

                        <div id="profil-actions" class="profil-actions-zone">
                            <button type="submit" id="btn-save-profil" class="btn-submit btn-save-full">Enregistrer les modifications</button>
                            <p id="profil-msg" class="profil-msg-text"></p>
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
                            <!-- Seul style en ligne autorisé car il dépend de la variable $points -->
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
                                $statut = $cmd['statut'] ?? 'Terminée'; 
                            ?>
                                <div class="order-history-item">
                                    <div class="order-history-header">
                                        <span>📅 <?php echo $date_affichage; ?></span>
                                        <span class="order-history-price"><?php echo number_format($cmd['total'], 2); ?> €</span>
                                    </div>
                                    
                                    <?php if(isset($cmd['type'])): ?>
                                        <div class="order-history-meta">
                                            <span>
                                                <?php echo ($cmd['type'] == 'Livraison' ? '🛵 ' : '🛍️ ') . htmlspecialchars($cmd['type']); ?> 
                                                - Prévu : <?php echo htmlspecialchars($cmd['date_prevue']); ?>
                                                <br>
                                                <span class="order-status-badge">Statut : <?php echo htmlspecialchars($statut); ?></span>
                                            </span>
                                            
                                            <div class="order-history-actions">
                                                <?php $vrai_index = count($historique) - 1 - $index_commande; ?>
                                                
                                                <?php if($statut === 'À préparer'): ?>
                                                    <a href="modifier_commande.php?id_cmd=<?php echo $vrai_index; ?>" class="btn-submit btn-modifier-small">✏️ Modifier</a>
                                                
                                                <?php elseif($statut === 'En préparation'): ?>
                                                    <span class="badge-en-cuisine">👨‍🍳 En cuisine...</span>

                                                <?php elseif($statut === 'Prête'): ?>
                                                    <span class="badge-prete">✅ Préparation terminée</span>
                                                
                                                <?php elseif ($statut === 'À récupérer'): ?>
                                                    <span class="badge-recuperer">🛍️ Prête ! Venez la récupérer.</span>
                                                    
                                                <?php elseif ($statut === 'En cours de livraison'): ?>
                                                    <span class="badge-livraison">🛵 Le livreur est en route !</span>

                                                <?php elseif($statut === 'Livrée' || $statut === 'Terminée'): ?>
                                                    
                                                    <?php if(isset($cmd['note'])): ?>
                                                        <span class="stars-rating-display" title="Votre note">
                                                            <?php echo str_repeat('★', $cmd['note']) . str_repeat('☆', 5 - $cmd['note']); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <button onclick="noterCommande(<?php echo $vrai_index; ?>)" class="btn-submit btn-noter-small">⭐ Noter</button>
                                                    <?php endif; ?>
                                                    
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <p class="order-history-articles">
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

<?php include 'includes/footer.php'; ?>