<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 🔒 SÉCURITÉ : On vérifie que la personne est bien admin ou restaurateur
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'restaurateur')) {
    header("Location: accueil.php");
    exit();
}

$page_title = "Africa United - Administration"; 
include 'includes/header.php'; 

// 1. On lit le fichier des utilisateurs
$json_data = file_get_contents('data/utilisateurs.json');
$utilisateurs = json_decode($json_data, true);

// 2. On crée une liste vide pour rassembler TOUTES les commandes
$toutes_les_commandes = [];

// 3. On fouille dans chaque utilisateur pour extraire ses commandes
foreach ($utilisateurs as $user) {
    if (!empty($user['fidelite']['historique_commandes'])) {
        foreach ($user['fidelite']['historique_commandes'] as $commande) {
            $commande['client'] = $user['informations']['prenom'] . ' ' . $user['informations']['nom'];
            $commande['email'] = $user['informations']['email'];
            $toutes_les_commandes[] = $commande;
        }
    }
}

$toutes_les_commandes = array_reverse($toutes_les_commandes);
?>

    <main class="admin-page admin-page-container">
        
        <div class="admin-header-zone">
            <h1 class="admin-main-title">👨‍🍳 Espace Administration</h1>
            <p>Bienvenue, <strong><?php echo $_SESSION['user_prenom']; ?></strong>. Voici le tableau de bord de votre restaurant.</p>
        </div>

        <section class="admin-section-block">
            <h2 class="admin-section-title-cuisine">📦 Gestion des Commandes (Cuisine)</h2>
            
            <?php 
            $livreurs = [];
            foreach($utilisateurs as $u) {
                if($u['role'] === 'livreur') $livreurs[] = $u;
            }
            ?>

            <table class="admin-table-cuisine">
                <thead class="admin-thead-dark">
                    <tr>
                        <th>Client</th>
                        <th>Articles</th>
                        <th class="text-center">Statut Actuel</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($utilisateurs as $user):
                        foreach ($user['fidelite']['historique_commandes'] ?? [] as $idx => $cmd):
                            $statut = $cmd['statut'] ?? 'Payée';
                    ?>
                    <tr class="ligne-commande admin-table-row">
                        <td>
                            <strong><?php echo htmlspecialchars($user['informations']['prenom']); ?></strong><br>
                            <span class="admin-client-email"><?php echo htmlspecialchars($user['informations']['email']); ?></span>
                        </td>
                        <td class="admin-cell-articles"><?php echo htmlspecialchars(implode(', ', $cmd['articles'])); ?></td>
                        <td class="text-center">
                            <span class="badge-statut admin-badge-statut">
                                <?php echo htmlspecialchars($statut); ?>
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="admin-actions-flex">
                                
                                <?php if($statut == 'À préparer' || $statut == 'Payée'): ?>
                                    <button onclick="changerStatut('<?php echo $user['informations']['email']; ?>', <?php echo $idx; ?>, 'En préparation')" class="btn-admin-launch">🔥 Lancer en cuisine</button>
                                
                                <?php elseif($statut == 'En préparation'): ?>
                                    <span class="status-text-cooking">👨‍🍳 En préparation...</span>
                                
                                <?php elseif($statut == 'Prête'): ?>
                                    <?php if(strpos(strtolower($cmd['type']), 'emporter') !== false): ?>
                                        <button onclick="changerStatut('<?php echo $user['informations']['email']; ?>', <?php echo $idx; ?>, 'À récupérer')" class="btn-admin-allow">🛍️ Autoriser le Retrait</button>
                                    <?php else: ?>
                                        <select onchange="assignerLivreur('<?php echo $user['informations']['email']; ?>', <?php echo $idx; ?>, this.value)" class="select-admin-driver">
                                            <option value="">Assigner livreur...</option>
                                            <?php foreach($livreurs as $l): ?>
                                                <option value="<?php echo $l['id']; ?>"><?php echo htmlspecialchars($l['informations']['prenom']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php endif; ?>

                                <?php elseif($statut == 'À récupérer'): ?>
                                    <button onclick="changerStatut('<?php echo $user['informations']['email']; ?>', <?php echo $idx; ?>, 'Terminée')" class="btn-admin-done">✅ Commande Retirée</button>
                                
                                <?php endif; ?>

                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endforeach; ?>
                </tbody>
            </table>
        </section>

        <section class="admin-section-block">
            <h2 class="admin-section-title-database">👥 Base de données Clients & Staff</h2>
            
            <table class="admin-table-database">
                <thead class="admin-thead-db">
                    <tr>
                        <th>ID</th>
                        <th>Client / Staff</th>
                        <th>Rôle</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($utilisateurs as $user): ?>
                    <tr class="admin-table-row">
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($user['informations']['prenom'] . ' ' . $user['informations']['nom']); ?></strong><br>
                            <span class="admin-client-email"><?php echo htmlspecialchars($user['informations']['email']); ?></span>
                        </td>
                        <td>
                            <strong class="role-badge role-<?php echo htmlspecialchars($user['role']); ?>">
                                <?php echo strtoupper(htmlspecialchars($user['role'])); ?>
                            </strong>
                        </td>
                        
                        <td class="text-center">
                            <?php if($user['role'] == 'client'): 
                                $remise_actuelle = $user['informations']['remise'] ?? 0;
                            ?>
                                
                                <select onchange="changerRemise('<?php echo $user['id']; ?>', this.value)" class="select-admin-remise">
                                    <option value="0" <?php if($remise_actuelle == 0) echo 'selected'; ?>>Pas de remise</option>
                                    <option value="10" <?php if($remise_actuelle == 10) echo 'selected'; ?>>Remise 10%</option>
                                    <option value="20" <?php if($remise_actuelle == 20) echo 'selected'; ?>>Remise 20%</option>
                                </select>
                                
                                <?php 
                                $est_bloque = isset($user['bloque']) && $user['bloque'] === true; 
                                $btn_text = $est_bloque ? "🔓 Débloquer" : "🚫 Bloquer";
                                $btn_class = $est_bloque ? "btn-admin-unblock" : "btn-admin-block"; 
                                ?>
                                <button onclick="bloquerUtilisateur('<?php echo $user['id']; ?>')" class="<?php echo $btn_class; ?>">
                                    <?php echo $btn_text; ?>
                                </button>

                                <button onclick="supprimerUtilisateur('<?php echo $user['id']; ?>')" class="btn-admin-delete" title="Supprimer définitivement">
                                    🗑️
                                </button>
                                
                            <?php else: ?>
                                <span class="text-not-applicable">Non applicable</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

<?php 
include 'includes/footer.php'; 
?>